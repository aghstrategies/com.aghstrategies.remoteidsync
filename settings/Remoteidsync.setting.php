<?php
/**
 * @file
 * Settings metadata for com.aghstrategies.customcivistylesui.
 * Copyright (C) 2016, AGH Strategies, LLC <info@aghstrategies.com>
 * Licensed under the GNU Affero Public License 3.0 (see LICENSE.txt)
 */
return array(
  'remoteidsync_sitekey' => array(
    'group_name' => 'Remote ID Sync Settings',
    'group' => 'remoteidsync',
    'name' => 'remoteidsync_sitekey',
    'type' => 'String',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Site Key',
    'help_text' => 'Site Key',
  ),
  'remoteidsync_apikey' => array(
    'group_name' => 'Remote ID Sync Settings',
    'group' => 'remoteidsync',
    'name' => 'remoteidsync_apikey',
    'type' => 'String',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'API Key of Remote User',
    'help_text' => 'API Key of Remote User',
  ),
  'remoteidsync_apiendpoint' => array(
    'group_name' => 'Remote ID Sync Settings',
    'group' => 'remoteidsync',
    'name' => 'remoteidsync_apiendpoint',
    'type' => 'String',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Endpoint for API',
    'help_text' => 'Endpoint for API',
  ),
);
