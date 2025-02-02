<?PHP
require 'MikroTikAPI.php';
require 'dbops.php';

/**
 * Retrieves a list of interfaces from a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @return array The list of interfaces or an error array.
 */
function readInterfaces($hostip, $apiport, $username, $password)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport); // Replace with your MikroTik IP and API port
        $api->login($username, $password); // Replace with your credentials

        // Retrieve list of interfaces
        $response = $api->sendCommand('/interface/print');

        $interfaces = [];
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $interface = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $interface[$key] = $value;
                    }
                }
                $interfaces[] = $interface;
            }
        }

        // Disconnect
        $api->disconnect();

        // Return the list of interfaces
        return $interfaces;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Retrieves a list of PPP profiles from a MikroTik router.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param bool $debug Optional. If true, prints debug information.
 * @return array The list of profiles or an error array.
 */
function readProfiles($hostip, $apiport, $username, $password, $debug = false)
{
    try {
        $api = new MikroTikAPI();

        if ($debug) echo "Connecting to MikroTik API at $hostip:$apiport...\n";

        // Connect to the router
        $api->connect($hostip, $apiport);
        if ($debug) echo "Connected successfully.\n";

        // Login to the router
        $api->login($username, $password);
        if ($debug) echo "Authentication successful.\n";

        // Retrieve list of PPP profiles
        $response = $api->sendCommand('/ppp/profile/print');
        if ($debug) echo "Command executed: /ppp/profile/print\n";

        $profiles = [];
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $profile = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $profile[$key] = $value;
                    }
                }
                $profiles[] = $profile;
            }
        }

        // Disconnect
        $api->disconnect();
        if ($debug) echo "Disconnected successfully.\n";

        // Return the list of profiles
        return $profiles;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Retrieves a list of PPP secrets from a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @return array The list of secrets or an error array.
 */
function readSecrets($hostip, $apiport, $username, $password)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport); // Replace with your MikroTik IP and API port
        $api->login($username, $password); // Replace with your credentials

        // Retrieve list of PPP secrets
        $response = $api->sendCommand('/ppp/secret/print');

        $secrets = [];
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $secret = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $secret[$key] = $value;
                    }
                }
                $secrets[] = $secret;
            }
        }

        // Disconnect
        $api->disconnect();

        // Return the list of secrets
        return $secrets;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Creates a PPP secret on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $secretName: The name of the secret.
 * - $secretPassword: The password for the secret.
 * - $additionalParams: An associative array of additional parameters (e.g., 'service', 'profile').
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $secretName The name of the secret.
 * @param string $secretPassword The password for the secret.
 * @param array $additionalParams Additional optional parameters.
 * @return array The result of the operation or an error array.
 */
function createSecret($hostip, $apiport, $username, $password, $secretName, $secretPassword, $additionalParams = [])
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Prepare the parameters for the /ppp/secret/add command
        $params = array_merge(
            [
                'name' => $secretName,
                'password' => $secretPassword,
            ],
            $additionalParams
        );

        // Execute the command to create the secret
        $response = $api->sendCommand('/ppp/secret/add', $params);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $response;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Disables a PPP secret on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $secretName: The name of the secret to disable.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $secretName The name of the secret to disable.
 * @return array The result of the operation or an error array.
 */
function disableSecret($hostip, $apiport, $username, $password, $secretName)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Retrieve all PPP secrets and find the one with the matching name
        $response = $api->sendCommand('/ppp/secret/print');

        $secretId = null;
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $data = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $data[$key] = $value;
                    }
                }

                // Check if the name matches
                if (isset($data['name']) && $data['name'] === $secretName) {
                    $secretId = $data['.id'];
                    break;
                }
            }
        }

        if (!$secretId) {
            $api->disconnect();
            return [
                'error',
                "Secret with name '$secretName' not found.",
                'Ensure the secret name is correct.'
            ];
        }

        // Disable the secret using the .id
        $disableResponse = $api->sendCommand('/ppp/secret/disable', [
            '.id' => $secretId
        ]);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $disableResponse;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Enables a PPP secret on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $secretName: The name of the secret to enable.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $secretName The name of the secret to enable.
 * @return array The result of the operation or an error array.
 */
function enableSecret($hostip, $apiport, $username, $password, $secretName)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Retrieve all PPP secrets and find the one with the matching name
        $response = $api->sendCommand('/ppp/secret/print');

        $secretId = null;
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $data = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $data[$key] = $value;
                    }
                }

                // Check if the name matches
                if (isset($data['name']) && $data['name'] === $secretName) {
                    $secretId = $data['.id'];
                    break;
                }
            }
        }

        if (!$secretId) {
            $api->disconnect();
            return [
                'error',
                "Secret with name '$secretName' not found.",
                'Ensure the secret name is correct.'
            ];
        }

        // Enable the secret using the .id
        $enableResponse = $api->sendCommand('/ppp/secret/enable', [
            '.id' => $secretId
        ]);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $enableResponse;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Deletes a PPP secret on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $secretName: The name of the secret to delete.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $secretName The name of the secret to delete.
 * @return array The result of the operation or an error array.
 */
function deleteSecret($hostip, $apiport, $username, $password, $secretName)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Retrieve all PPP secrets and find the one with the matching name
        $response = $api->sendCommand('/ppp/secret/print');

        $secretId = null;
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $data = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $data[$key] = $value;
                    }
                }

                // Check if the name matches
                if (isset($data['name']) && $data['name'] === $secretName) {
                    $secretId = $data['.id'];
                    break;
                }
            }
        }

        if (!$secretId) {
            $api->disconnect();
            return [
                'error',
                "Secret with name '$secretName' not found.",
                'Ensure the secret name is correct.'
            ];
        }

        // Delete the secret using the .id
        $deleteResponse = $api->sendCommand('/ppp/secret/remove', [
            '.id' => $secretId
        ]);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $deleteResponse;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Creates a PPP profile on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $profileName: The name of the profile.
 * - $additionalParams: An associative array of additional parameters (e.g., 'local-address', 'remote-address').
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $profileName The name of the profile.
 * @param array $additionalParams Additional optional parameters.
 * @return array The result of the operation or an error array.
 */
function createProfile($hostip, $apiport, $username, $password, $profileName, $additionalParams = [])
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Prepare the parameters for the /ppp/profile/add command
        $params = array_merge(
            [
                'name' => $profileName,
            ],
            $additionalParams
        );

        // Execute the command to create the profile
        $response = $api->sendCommand('/ppp/profile/add', $params);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $response;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Creates an IP pool on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $poolName: The name of the IP pool.
 * - $addresses: The range of addresses for the pool (e.g., "192.168.1.10-192.168.1.100").
 * - $additionalParams: An associative array of additional parameters (e.g., 'next-pool').
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $poolName The name of the IP pool.
 * @param string $addresses The range of addresses for the pool.
 * @param array $additionalParams Additional optional parameters.
 * @return array The result of the operation or an error array.
 */
function createIPPool($hostip, $apiport, $username, $password, $poolName, $addresses, $additionalParams = [])
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Prepare the parameters for the /ip/pool/add command
        $params = array_merge(
            [
                'name' => $poolName,
                'ranges' => $addresses
            ],
            $additionalParams
        );

        // Execute the command to create the IP pool
        $response = $api->sendCommand('/ip/pool/add', $params);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $response;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Retrieves a list of IP pools from a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @return array The list of IP pools or an error array.
 */
function readIPPools($hostip, $apiport, $username, $password)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Retrieve list of IP pools
        $response = $api->sendCommand('/ip/pool/print');

        $pools = [];
        foreach ($response as $sentence) {
            if (!empty($sentence) && $sentence[0] === '!re') {
                $pool = [];
                foreach ($sentence as $word) {
                    if (strpos($word, '=') === 0) {
                        [$key, $value] = explode('=', substr($word, 1), 2);
                        $pool[$key] = $value;
                    }
                }
                $pools[] = $pool;
            }
        }

        // Disconnect
        $api->disconnect();

        // Return the list of IP pools
        return $pools;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Creates a VLAN interface on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $vlname: The name of the VLAN interface to be created.
 * - $vlid: The VLAN ID (VLAN number).
 * - $prntif: The parent interface on which the VLAN will be created.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $vlname The name of the VLAN interface.
 * @param int $vlid The VLAN ID.
 * @param string $prntif The parent interface.
 * @return array The result of the operation or an error array.
 */
function createVlanInterface($hostip, $apiport, $username, $password, $vlname, $vlid, $prntif)
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Prepare the parameters for the /interface/vlan/add command
        $params = [
            'name' => $vlname,
            'vlan-id' => $vlid,
            'interface' => $prntif
        ];

        // Execute the command to create the VLAN interface
        $response = $api->sendCommand('/interface/vlan/add', $params);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $response;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

/**
 * Creates a PPPoE service on a MikroTik router.
 *
 * Params:
 * - $hostip: The IP address of the MikroTik router.
 * - $apiport: The API port (default: 8728).
 * - $username: The API username.
 * - $password: The API password.
 * - $srvname: The name of the PPPoE service.
 * - $ifname: The interface on which the PPPoE service will be enabled.
 *
 * @param string $hostip The IP address of the MikroTik router.
 * @param int $apiport The API port (default: 8728).
 * @param string $username The API username.
 * @param string $password The API password.
 * @param string $srvname The name of the PPPoE service.
 * @param string $ifname The interface for the PPPoE service.
 * @return array The result of the operation or an error array.
 */
function createPppoeService($hostip, $apiport, $username, $password, $srvname, $ifname, $dfltProf='default')
{
    try {
        $api = new MikroTikAPI();

        // Connect to the router
        $api->connect($hostip, $apiport);
        $api->login($username, $password);

        // Prepare the parameters for the /interface/pppoe-server/add command
        $params = [
            'service-name' => $srvname,
            'interface' => $ifname,
            'one-session-per-host' => 'yes', // Enable one session per host
            'disabled' => 'no',              // Ensure the service is enable
            'authentication' => 'pap',
            'default-profile' => $dfltProf
        ];

        // Execute the command to create the PPPoE service
        $response = $api->sendCommand('/interface/pppoe-server/server/add', $params);

        // Disconnect
        $api->disconnect();

        // Return the API response
        return $response;

    } catch (Exception $e) {
        // Return error information
        return [
            'error',
            $e->getMessage(),
            'Ensure the MikroTik router is reachable and credentials are correct.'
        ];
    }
}

