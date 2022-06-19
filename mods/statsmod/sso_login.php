<?php

$debug=false;

unset($_SESSION['sso_error']);

if (!isset($_GET['page'])) {
    $page = config::get("cfg_kbhost");
} else {
    $page = $_GET['page'];
}

function redirect_post($url) {
    $html = "<head><style>body {background-color: black;}</style></head>";
    $html .= "<form action='".$url."' method='post' name='frm'><input type='hidden' name='bypassCache' value='True ".microtime()."'></form>";
    $html .= "<script type='text/javascript'>document.frm.submit();</script>";
    echo $html;
}

function fixObject (&$object)
{
    if (!is_object ($object) && gettype ($object) == 'object')
        return ($object = unserialize (serialize ($object)));
    return $object;
}

function login($page, $method='login') {
    require_once('vendor/autoload.php');
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['access_token'])) {
        $_SESSION['access_token'] = fixObject($_SESSION['access_token']);
    }
    global $provider;
    if(config::get("cfg_pathinfo") == '1') {	
        $callback = edkURI::page('sso_login/?method='.$method.'&page='.rawurlencode(rtrim($page, '/')));
    } else {
        $callback = edkURI::page('sso_login&method='.$method.'&page='.rawurlencode(rtrim($page, '/')));
    }
    $provider = new Evelabs\OAuth2\Client\Provider\EveOnline([
        'clientId'          => config::get('statsmod_sso_client_id'),
        'clientSecret'      => config::get('statsmod_sso_secret'),
        'redirectUri'       => $callback,
    ]);

    $options = [
       // 'scope' => ['publicData', 'esi-killmails.read_killmails.v1', 'esi-killmails.read_corporation_killmails.v1', 'esi-fittings.read_fittings.v1'] // array or string
     'scope' => ['publicData']
	];
    
    if (isset($_SESSION['access_token'])) {
        $token = $_SESSION['access_token'];
        if ($token->hasExpired()) {
            try {
                $token = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $_SESSION['access_token']->getRefreshToken()
                ]);
                $_SESSION['access_token'] = $token;
            } catch (Exception $e) {
                $_SESSION['oauth2state'] = $provider->getState();
                header('Location: '.$authUrl);
            }
        }
        $authUrl = $provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $provider->getState();
    } elseif (!isset($_GET['code'])) {
        // If we don't have an authorization code redirect
            $authUrl = $provider->getAuthorizationUrl($options);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
    // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        $_SESSION['sso_error'] = "CREST: Invalid state.";
        redirect_post($page);
    } else {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
             'code' => $_GET['code']
        ]);
        $_SESSION['access_token'] = $token;
    }

    if (isset($token)) {
        $user = $provider->getResourceOwner($token);
        $userID = $user->getCharacterID();
        $userName = $user->getCharacterName();
        $sso_char = Pilot::lookup($userName);
        if (isset($sso_char) && $sso_char !== false) {
            $_SESSION['sso_char'] = $sso_char;
        } else {
            try {
                $request = $provider->getRequest(
                    'GET',
                    'https://crest-tq.eveonline.com/characters/'.$userID.'/'
                );
                $response = $provider->getResponse($request);
                if (isset($response)) {
                    $timestamp = date("Y-m-d H:i:s", gmmktime());
                    $corporationID=$response['corporation']['id'];
                    $corp = new Corporation($corporationID, true);
                    $sso_char = Pilot::add($userName, $corp, $timestamp, $userID);
                    $_SESSION['sso_char'] = $sso_char;
                } else {
                    unset($_SESSION['access_token']);
                    unset($_SESSION['oauth2state']);
                    $_SESSION['sso_error'] = "No character details returned from CREST";
                }
            } catch (Exception $e) {
                unset($_SESSION['access_token']);
                unset($_SESSION['oauth2state']);
                $_SESSION['sso_error'] = "Something went wrong contacting CREST, Please Try again.";
            }
        }
    }
}

if ($_GET['method'] == 'logout') {
    unset($_SESSION['access_token']);
    unset($_SESSION['oauth2state']);
    unset($_SESSION['sso_char']);
    header('Location: '.htmlspecialchars_decode($page));
} elseif ($_GET['method'] == 'login') {
    login($page);
} elseif ($_GET['method'] == 'postkills') {
    login($page, 'postkills');
    $request = $provider->getAuthenticatedRequest(
        'GET',
        'https://crest-tq.eveonline.com/characters/'.$_SESSION['sso_char']->getExternalID().'/',
        $_SESSION['access_token']->getToken()
    );

    $response = $provider->getResponse($request);
    
    //$data = $api->CallAPI($scope, $sheet, array('characterID' => $_SESSION['sso_char']->getExternalID(), 'accessToken' => (string)$_SESSION['access_token']), null, null);
    //$data = $pheal->$scope->$sheet(array('characterID' => $_SESSION['sso_char']->getExternalID(), 'accessToken' => (string)$_SESSION['access_token']));
    require_once('class.ssokilllog.php');
    $ssoEveAPI = new SSO_KillLog();
    $output = $ssoEveAPI->Import($_SESSION['sso_char']->getName(), $_SESSION['sso_char']->getExternalID(), (string)$_SESSION['access_token'], 'char');
    $resultspage = new Page( "API KillLog using the SSO" );
    $html .= "<br/>";
    $html .= $output;
    $html .= '<form method="post" action="'.$page.'"><input type="submit" value="Click to return" /></form>';
    $resultspage->setContent( $html );
    $resultspage->generate();
    die();
} elseif ($_GET['method'] == 'savefit') {
    $output = '';
    if (isset($_GET['fit'])) {
        include_once('class.jsonfitting.php');
        $jsonfit = jsonfitting::createfromid((int)rtrim($_GET['fit'], '/'));
        $_SESSION['sso_fit'] = $jsonfit;
    } elseif (isset($_SESSION['sso_fit'])) {
        $jsonfit = $_SESSION['sso_fit'];
    } else {
        $output .= "Error: No fitting data.";
    }
    login($page, 'savefit');
    $request = $provider->getAuthenticatedRequest(
        'POST',
        'https://crest-tq.eveonline.com/characters/'.$_SESSION['sso_char']->getExternalID().'/fittings/',
        $_SESSION['access_token']->getToken(),
        array('body' => $jsonfit, 'headers' => 'Content-Type:application/json')
    );
    unset($_SESSION['sso_fit']);
    try {
        $response = $provider->getResponse($request);
    } catch(Exception $e) {
        $response = $e->getMessage();
        $caught = true;
        $output .= "Something went wrong: ".$response; 
    }
    if (!$caught) $output .= "Fitting saved to character ".$_SESSION['sso_char']->getName()." successfully.";
    $resultspage = new Page( "CREST fit saving" );
    $html = "<br/>";
    $html .= $output;
    $html .= '<br/><br/><form method="post" action="'.$page.'"><input type="submit" value="Click to return" /></form>';
    $resultspage->setContent( $html );
    $resultspage->generate();
    die();
}
redirect_post($page);
?> 
