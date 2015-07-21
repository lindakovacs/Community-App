<?php 

class PL_Compliance {

	public static function mls_message ($args) {

		extract(wp_parse_args($args, array(
					'context' => false,
					'agent_name' => false,
					'office_name' => false,
					'office_phone' => false,
					'agent_license' => false,
					'co_agent_name' => false,
					'co_office_name' => false,
					'provider_id' => false
				)
			)
		);

		$whoami = PL_Helper_User::whoami();
		//if this account has multiple providers. Accounts with just one appear in "provider"
		//if the requester passes in a provider_id, try to match it, else, do nothing.
		if ( $whoami['providers'] && $provider_id && isset($whoami['providers'][$provider_id] ) )

			//if the provider id matches, just set it to "provider" since that's what the rest of the code uses.
			//validate it too!
			$whoami['provider'] = PL_Validate::attributes($whoami['providers'][$provider_id], PL_Config::PL_API_USERS('whoami', 'returns', 'provider'));

		if ( !empty($whoami['provider']['disclaimer_on']) || !empty($whoami['provider']['office_on']) || !empty($whoami['provider']['agent_on']) ) {
			$provider = $whoami['provider'];
			$response = array();

			// massage $provider['disclaimer'] by replacing {brokerage_name} with the actual brokerage name.
			if ($provider['disclaimer']) {
				$company_name = $whoami['name'] ?: 'the publisher of this website';
				$provider['disclaimer'] = str_replace( '{brokerage_name}', $company_name, $provider['disclaimer'] );
			}

			// check for co_agent_name and co_office_name being set to "n/a," which we do not want
			if ($co_agent_name) {
				$co_agent_name = trim($co_agent_name);
				if (strtolower($co_agent_name) == 'n/a')
					$co_agent_name = false;
			}
			if ($co_office_name) {
				$co_office_name = trim( $co_office_name );
				if (strtolower( $co_office_name ) == 'n/a')
					$co_office_name = false;
			}

			// pdp_* contexts are configured by the settings for 'listings'
			$config = ((strpos($context, 'pdp_') === 0) ? 'listings' : $context);

			// do we need the disclaimer?
			if ($config == $context || $context == 'pdp_disclaimer') {
				$response['last_import'] = date_format(date_create($provider['last_import']), "jS F, Y g:i A.");
				if (isset($provider['disclaimer_on'][$config]) && !empty($provider['disclaimer_on'][$config])) {
					$response['disclaimer'] = $provider['disclaimer'];
					$response['img'] = $provider['first_logo'];
				}
			}

			// which fields to show?
			if (isset($provider['agent_on'][$config]) && !empty($provider['agent_on'][$config]) && $agent_name) {
				$response['agent_name'] = $agent_name;
				$response['agent_license'] = $agent_license;
				$response['co_agent_name'] = $co_agent_name;
				$response['co_office_name'] = $co_office_name;
			}
			if (isset($provider['office_on'][$config]) && !empty($provider['office_on'][$config]) && $office_name)
				$response['office_name'] = $office_name;
			if (isset($provider['office_phone_on'][$config]) && !empty($provider['office_phone_on'][$config]) && $office_phone)
				$response['office_phone'] = $office_phone;

			return $response;
		}
		return false;
	}
}