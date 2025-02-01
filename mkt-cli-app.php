<?PHP
define("HOST_IP", "");
define("API_PORT", "");
define("USR_NAME", "");
define("API_PASS", "");

require 'funcs.php';
global $loginInfo;





/**
 * Extracts the first IP of the first network from a given IP range.
 *
 * @param string $ipRange The IP range in format "startIP-endIP".
 * @return string The first IP of the network.
 * @throws Exception If the range format is invalid.
 */

function getFirstNetworkIP($ipRange)
{
    // Split the range into start and end IPs
    $parts = explode('-', $ipRange);
    
    if (count($parts) !== 2) {
        throw new Exception("Invalid IP range format. Expected format: 'X.X.X.X-Y'");
    }

    // Extract the first IP and split into octets
    $ipOctets = explode('.', $parts[0]);

    if (count($ipOctets) !== 4) {
        throw new Exception("Invalid start IP format.");
    }

    // Set the last octet to 1
    $ipOctets[3] = '1';

    // Reconstruct the IP
    return implode('.', $ipOctets);
}

function createSingleIpPool() {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $vid = '1044';
    $prefix = 'GRCT';
    $poolName = $prefix . '-' . $vid . '-' . 'IP-POOL';
    $range = "10.40.21.3-10.40.22.250";
    $result = createIPPool($hostip, $apiport, $username, $password, $poolName, $range);
}
function transferIpPools() {
    $srcRt = '192.168.0.65';
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $pools = readIPPools($srcRt, $apiport, $username, $password);
    foreach ($pools as $pool) {
        // var_dump($pool);
        $name = $pool['name'];
        $range = $pool['ranges'];
        $rpt = createIPPool($hostip, $apiport, $username, $password, $name, $range);
        var_dump($rpt);
    }
}

function radDbtoSecret() {
    $dbPath = '/home/<username>/MyProjects/2024_fresh/db_files/pe_subscriber_billing/pe_subscriber_billing.slite3';
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $sqlString = 'SELECT * from rad_login_entry';

    $records = simpleQuerySqlite($dbPath, $sqlString);
    foreach ($records as $record) {
        // var_dump($record);
        $secretname = $record['ppp_user_name'];
        $secretpass = $record['ppp_password'];
        $pppserver = $record['pppoe_server'];
        $profile = $pppserver . '-dflt-Prof';
        // echo $secretname . ' ' . $secretpass . ' ' . $pppserver . ' ' . $profile . PHP_EOL;
        $additionalParams = [
            'service' => 'pppoe',  // Set Service to PPPoE
            'profile' => $profile // Assign a Profile (replace 'default' with actual profile name)
        ];
        createSecret($hostip, $apiport, $username, $password, $secretname, $secretpass, $additionalParams);
    }
}

function createSingleVlan() {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $vlid = 1044;
    $vlname = 'GRCT-' . $vlid . '-VL';
    $prntif = 'F-1';
    $rslt = createVlanInterface($hostip, $apiport, $username, $password, $vlname, $vlid, $prntif);
    var_dump($rslt);
}

function createSinglePpppoeService() {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $srvname = 'GRCT-1034-PPPOE-SRV';
    $ifname = 'GRCT-1034-VL';
    $stat = createPppoeService($hostip, $apiport, $username, $password, $srvname, $ifname);
    var_dump($stat);
}

function createIpPoolWithVidRange($svid, $prfx='NotAvl', $oct2='10.47', $s3oct=1, $bundle=10) {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    for ($i=0; $i <= $bundle; $i++) {
        $thisVid = $svid + $i;
        $rngs = $oct2 . '.' . ($s3oct + $i*2) . '.3'; 
        $rnge = $oct2 . '.' . ($s3oct + ($i*2)+1) . '.250';
        $rng = $rngs . '-' . $rnge;
        $poolName = $prfx . '-' . $thisVid . '-IP-POOL';
        // echo $poolName . PHP_EOL;
        // echo $thisVid . PHP_EOL;
        // echo $rng . PHP_EOL;
        createIPPool($hostip, $apiport, $username, $password, $poolName, $rng);
    }
}

function createProfilesOnPools() {
    $rtIp = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    $pools = readIPPools($rtIp, $apiport, $username, $password);
    foreach ($pools as $pool) {
        // var_dump($pool);
        $poolname = $pool['name'];
        $parts = explode('-', $poolname);
        $prfx = $parts[0] . '-'. $parts[1];
        $range = $pool['ranges'];
        $firstIP = '';
        $firstIP = getFirstNetworkIP($range);
        // echo 'Range-> ' . $range . ' First IP-> ' . $firstIP . PHP_EOL;
        
        $profParams = [
            'local-address' => $firstIP,   // Set Local Address as a specific IP
            'remote-address' => $poolname   // Set Remote Address as an IP Pool
        ];

        $profname = $prfx . '-DFLT-PROF';
        createProfile($rtIp, $apiport, $username, $password, $profname, $profParams);

        $profname = $prfx . '-5mb-PROF';
        createProfile($rtIp, $apiport, $username, $password, $profname, $profParams);

        $profname = $prfx . '-10mb-PROF';
        createProfile($rtIp, $apiport, $username, $password, $profname, $profParams);

        $profname = $prfx . '-15mb-PROF';
        createProfile($rtIp, $apiport, $username, $password, $profname, $profParams);

        $profname = $prfx . '-20mb-PROF';
        createProfile($rtIp, $apiport, $username, $password, $profname, $profParams);
    }
}

function createVlanRange($strtvid, $endvid, $prefix, $prntif) {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    for ($nv = $strtvid; $nv <= $endvid; $nv++ ) {
        $vlname = $prefix . '-' . $nv . '-VLAN-IF';
        $rslt = createVlanInterface($hostip, $apiport, $username, $password, $vlname, $nv, $prntif);
    }
}

function createPppoeServiceRange($svlan, $evlan, $prefix) {
    $hostip = HOST_IP;
    $apiport = API_PORT;
    $username = USR_NAME;
    $password = API_PASS;
    for ($nv = $svlan; $nv <= $evlan; $nv++ ) {
        $srvname = $prefix . '-' . $nv . '-PPPOE-SRV';
        $vifname = $prefix . '-' . $nv . '-VLAN-IF';
        $dfltProf = $prefix. '-' . $nv . '-DFLT-PROF';
        $stat = createPppoeService($hostip, $apiport, $username, $password, $srvname, $vifname, $dfltProf);
        var_dump($stat);
    }
}

createIpPoolWithVidRange(1034, 'KhnIsp', '10.47', 1, 20);
createProfilesOnPools();
createVlanRange(1034, 1044, 'KhnIsp', '00-F-2');
createPppoeServiceRange(1034, 1044, 'KhnIsp');
