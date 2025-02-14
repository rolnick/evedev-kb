<?php

/**
 * API KillMails - /corp/KillMails.xml.aspx
 * does not suffer from the terrible account-wide caching, always returns valid results
 */

define('NUMBER_OF_CALLS_DEFAULT', 1);

class SSO_KillLog extends API
{
	function Import($name, $charid, $accessToken, $type)
	{
		$output = "";

		// reduces strain on DB
		if(function_exists("set_time_limit")) {
      		set_time_limit(0);
		}

		$logsource = "New XML";
		// Load new XML
		$output = "<i>Downloading latest XML file for $name</i><br><br>";

		$posted = array();
		$skipped = array();

                // get maximum number of API calls per key
                $numberOfCallsMax = config::get('apikillmails_numberofcalls');
                if(!$numberOfCallsMax)
                {
                    $numberOfCallsMax = NUMBER_OF_CALLS_DEFAULT;
                }

		$oldestKill = 0;
                $mailsDepleted = FALSE;
                $numberOfCalls = 0;
                
		while (!$mailsDepleted && $numberOfCalls < $numberOfCallsMax) {
                        $numberOfCalls++;
			$args = array("characterID" => $charid, 'accessToken' => $accessToken);
			if ($oldestKill > 0) {
				$args["fromID"] = $oldestKill;
			}
			if ($type == 'corp') {
				$killLog = $this->CallAPI("corp", "KillMails", $args, null, null);
			} else if ($type == 'char') {
				$killLog = $this->CallAPI("char", "KillMails", $args, null, null);
			} else {
				$output .= "<div class='block-header2'>Access Token does not have access to KillLog</div>";
				break;
			}

			if ($this->getError() === null) {
				// Get oldest kill
				$oldestKill = 0;
				$sxe = simplexml_load_string($this->pheal->xml);
                                if(count($sxe->result->rowset->row) > 0)
                                {
                                    $oldestKill = (int) $sxe->result->rowset->row[0]['killID'];
                                }
                                
                                else
                                {
                                    $mailsDepleted = TRUE;
                                    break;
                                }
                                
				foreach ($sxe->result->rowset->row as $row) {
					if ($oldestKill > (int) $row['killID']) {
						$oldestKill = (int) $row['killID'];
					}
				}
			}

			if ($this->getError() !== null) {
				if ($this->getError() == 120 && $this->pheal->xml) {
					// Check if we just need to skip back a few kills
					// i.e. first page of kills is already fetched.
					$pos = strpos($this->pheal->xml, "Expected fromID [");
					if ($pos) {
						$pos += 23;
						$pos2 = strpos($this->pheal->xml, "]", $pos);
						$oldestKill = (int) substr($this->pheal->xml, $pos, $pos2 - $pos);
					}
				} else if (!$posted && !$skipped) {
					// Something went wrong and no kills were found.
					$qry = DBFactory::getDBQuery();
					$logtype = "Cron Job";

					$qry->execute("insert into kb3_apilog	values( '".KB_SITE."', '"
									.addslashes($name)."',"
									."0, "
									."0, "
									."0, "
									."0, "
									."0, '"
									."Error','"
									."Cron Job',"
									.$this->getError().", "
									."UTC_TIMESTAMP() )");
					$output .= "<div class='block-header2'>".$this->getMessage()
									."</div>";
					break;
				} else {
					// We found kills!
					$qry = DBFactory::getDBQuery();
					$logtype = "Cron Job";

					$qry->execute("insert into kb3_apilog values( '".KB_SITE."', '"
									.addslashes($name)."',"
									.count($posted).","
									."0 ,"
									.count($skipped).","
									."0 ,"
									.(count($posted) + count($skipped)).",'"
									."New XML','"
									."Cron Job',"
									.($this->getError() == 119 ? 0 : $this->getError()).", "
									."UTC_TIMESTAMP() )");

					break;
				}
			}
                        
			$feedfetch = new IDFeed();
			$feedfetch->setXML($this->pheal->xml);
			$feedfetch->setLogName("API");
			$feedfetch->read();

			$posted = $feedfetch->getPosted();
			$skipped = $feedfetch->getSkipped();
                        
                        if ($this->getError() == null)
                        {
                            // We found kills!
                            $qry = DBFactory::getDBQuery();

                            $qry->execute("insert into kb3_apilog values( '".KB_SITE."', '"
                                                            .addslashes($name)."',"
                                                            .count($posted).","
                                                            ."0 ,"
                                                            .count($skipped).","
                                                            ."0 ,"
                                                            .(count($posted) + count($skipped)).",'"
                                                            ."New XML','"
                                                            ."Cron Job',
                                                            0, 
                                                            UTC_TIMESTAMP() )");
                        }

			$output .= "<div class='block-header2'>"
							.count($posted)." kill".(count($posted) == 1 ? "" : "s")." posted, "
							.count($skipped)." skipped.<br></div>";
                        if ($feedfetch->getParseMessages()) {
                                $output .= implode("<br />", $feedfetch->getParseMessages());
                        }
			if (count($posted)) {
				$output .= "<div class='block-header2'>Posted</div>\n";
				foreach ($posted as $killid) {
					$output .= "<div><a href='"
									.edkURI::page('kill_detail', $killid[2], 'kll_id')
									."'>Kill ".$killid[0]."</a></div>";
				}
			}
		}
		return $output;
	}
}
