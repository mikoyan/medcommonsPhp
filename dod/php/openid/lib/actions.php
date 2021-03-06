<?php

require_once "lib/common.php";
require_once "lib/session.php";
require_once "lib/render.php";

require_once "lib/render/login.php";
require_once "lib/render/idpage.php";
require_once "lib/render/idpXrds.php";
require_once "lib/render/userXrds.php";

require_once "Auth/OpenID.php";

require_once 'mc.inc.php';

function get_mcid_from_url($url) {
    $a = explode('/', $url);
    $i = count($a);
    while ($i > 0) {
        $i--;
        if (is_valid_mcid($a[$i]))
            return pretty_mcid($a[$i]);
    }
    return null;
}

/**
 * Handle a standard OpenID server request
 */
function action_default()
{
    header('X-XRDS-Location: '.buildURL('idpXrds'));

    $server =& getServer();
    $request = $server->decodeRequest();

    if (!$request) {
        return about_render();
    }

    setRequestInfo($request);

    if (in_array($request->mode,
                 array('checkid_immediate', 'checkid_setup'))) {
        if ($request->idSelect()) {
            // Perform IDP-driven identifier selection
            if ($request->mode == 'checkid_immediate') {
                $response =& $request->answer(false);
            } else {
                return trust_render($request);
            }
        } else if ((!$request->identity) &&
                   (!$request->idSelect())) {
            // No identifier used or desired; display a page saying
            // so.
            return noIdentifier_render();
        } else if ($request->immediate) {
            $response =& $request->answer(false, buildURL());
        } else if (!getLoggedInUser()) {
            return login_render(null, get_mcid_from_url($request->identity));
        } else {
            return trust_render($request);
        }
    } else {
        $response =& $server->handleRequest($request);
    }

    $webresponse =& $server->encodeResponse($response);

    foreach ($webresponse->headers as $k => $v) {
        header("$k: $v");
    }

    header(header_connection_close);
    print $webresponse->body;
    exit(0);
}

/**
 * Log out the currently logged in user
 */
function action_logout()
{
    setLoggedInUser(null);
    setRequestInfo(null);
    return authCancel(null);
}

/**
 * Check the input values for a login request
 */
function login_checkInput($input)
{
    $openid_url = false;
    $errors = array();

    if (!isset($input['mcid'])) {
        $errors[] = 'Enter an OpenID URL to continue';
    }
    if (!isset($input['password'])) {
        $errors[] = 'Enter a valid password to continue';
    }
    else if ($input['password'] != 'valid') {
        $errors[] = 'Enter a valid password to continue';
    }
    if (count($errors) == 0) {
        $openid_url = 'http://garble.com/' . $input['mcid'];
    }
    return array($errors, $openid_url);
}

/**
 * Log in a user and potentially continue the requested identity approval
 */
function action_login()
{
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
    case 'GET':
        return login_render();
    case 'POST':
        $info = getRequestInfo();
        $fields = $_POST;
        if (isset($fields['cancel'])) {
            return authCancel($info);
        }

        list ($errors, $openid_url) = login_checkInput($fields);
        if (count($errors) || !$openid_url) {
            $needed = $info ? $info->identity : false;
            return login_render($errors, @$fields['mcid'], $needed);
        } else {
            setLoggedInUser($openid_url);
            return doAuth($info);
        }
    default:
        return login_render(array('Unsupported HTTP method: $method'));
    }
}

/**
 * Ask the user whether he wants to trust this site
 */
function action_trust()
{
    $info = getRequestInfo();
    $trusted = isset($_POST['trust']);
    return doAuth($info, $trusted, true, @$_POST['idSelect']);
}

function action_idpage()
{
    $identity = $_GET['user'];
    return idpage_render($identity);
}

function action_idpXrds()
{
    return idpXrds_render();
}

function action_userXrds()
{
    $identity = $_GET['user'];
    return userXrds_render($identity);
}

?>
