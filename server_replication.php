<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Server replications
 *
 * @package PhpMyAdmin
 */

use PhpMyAdmin\ReplicationGui;
use PhpMyAdmin\Response;
use PhpMyAdmin\Server\Common;

/**
 * include files
 */
require_once 'libraries/common.inc.php';
require_once 'libraries/server_common.inc.php';
require_once 'libraries/replication.inc.php';

/**
 * Does the common work
 */
$response = Response::getInstance();
$header   = $response->getHeader();
$scripts  = $header->getScripts();
$scripts->addFile('server_privileges.js');
$scripts->addFile('replication.js');
$scripts->addFile('vendor/zxcvbn.js');

/**
 * Checks if the user is allowed to do what he tries to...
 */
if (! $GLOBALS['dbi']->isSuperuser()) {
    $html  = Common::getHtmlForSubPageHeader('replication');
    $html .= PhpMyAdmin\Message::error(__('No Privileges'))->getDisplay();
    $response->addHTML($html);
    exit;
}

// change $GLOBALS['url_params'] with $_REQUEST['url_params']
// only if it is an array
if (isset($_REQUEST['url_params']) && is_array($_REQUEST['url_params'])) {
    $GLOBALS['url_params'] = $_REQUEST['url_params'];
}

/**
 * Handling control requests
 */
ReplicationGui::handleControlRequest();

/**
 * start output
 */
$response->addHTML('<div id="replication">');
$response->addHTML(Common::getHtmlForSubPageHeader('replication'));

// Display error messages
$response->addHTML(ReplicationGui::getHtmlForErrorMessage());

if ($GLOBALS['replication_info']['master']['status']) {
    $response->addHTML(ReplicationGui::getHtmlForMasterReplication());
} elseif (! isset($_REQUEST['mr_configure'])
    && ! isset($_REQUEST['repl_clear_scr'])
) {
    $response->addHTML(ReplicationGui::getHtmlForNotServerReplication());
}

if (isset($_REQUEST['mr_configure'])) {
    // Render the 'Master configuration' section
    $response->addHTML(ReplicationGui::getHtmlForMasterConfiguration());
    exit;
}

$response->addHTML('</div>');

if (! isset($_REQUEST['repl_clear_scr'])) {
    // Render the 'Slave configuration' section
    $response->addHTML(
        ReplicationGui::getHtmlForSlaveConfiguration(
            $GLOBALS['replication_info']['slave']['status'],
            $server_slave_replication
        )
    );
}
if (isset($_REQUEST['sl_configure'])) {
    $response->addHTML(ReplicationGui::getHtmlForReplicationChangeMaster("slave_changemaster"));
}
