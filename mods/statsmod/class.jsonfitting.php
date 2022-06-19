<?php
class jsonfitting
    {
    static function createfromid($id)
            {
                    $km = Cacheable::factory('Kill', $id);

                    if (!$km->exists()) {
                        return '';
                    }
                    $ship=$km->getVictimShip();
                    $shipclass=$ship->getClass();
                    $shipname = $ship->getName();
                    $fittingarray = array();
                    $fittingarray['name'] = "EDK ".$shipname;
                    $fittingarray['description'] = "Imported from ".edkURI::page('kill_detail', $id);
                    $fittingarray['items'] = array();
                    $items = array_merge($km->getDestroyedItems(), $km->getDroppedItems());
                    $slots = array_merge(range(11, 34), range(87,87), range(92, 98), range(125, 132));
                    $slots_avail = $slots;
                    foreach ($items as $item) {
                        //print $item->getItem()->getName()." ".$item->getLocationID()."<br/>";
                        $tmp = array();
                        $tmp['type'] = array('id' => (int)$item->getItem()->getID(), 'name' => $item->getItem()->getName(), "href" => "https://crest-tq.eveonline.com/inventory/types/".$item->getItem()->getID()."/");
                        $tmp['flag'] = (int)$item->getLocationID();
                        $tmp['quantity'] = (int)$item->getQuantity();
                        if (in_array($tmp['flag'], $slots)) {
                            //if (in_array($tmp['flag'], $slots_avail)) {
                                array_push($fittingarray['items'], $tmp);
                            //    $slots_avail = array_diff($slots_avail, array($tmp['flag']));
                            //}
                        }
                    }
                    $fittingarray['ship'] = array('id' => (int)$ship->getID(), 'name' => $shipname, "href" => "https://crest-tq.eveonline.com/inventory/types/".$ship->getID()."/");
                    $jsonfitting = json_encode($fittingarray);
                    return $jsonfitting;
            }
    }
?>
