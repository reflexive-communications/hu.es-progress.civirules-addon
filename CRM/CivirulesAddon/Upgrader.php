<?php

/**
 * Collection of upgrade steps.
 */
class CRM_CivirulesAddon_Upgrader extends CRM_CivirulesAddon_Upgrader_Base
{

    /**
     * Returns true if CiviRules is installed.
     *
     * @return bool
     */
    public function civirulesIsInstalled()
    {
        $result = civicrm_api3(
            'Extension',
            'get',
            [
                'is_active' => 1,
                'full_name' => "org.civicoop.civirules",
            ]
        );

        return ($result['count'] == 1);
    }

    /**
     * Create Civirules Condition
     */
    protected function installCiviRuleCondition()
    {
        if ($this->civirulesIsInstalled()) {
            civicrm_api3(
                'CiviRuleCondition',
                'create',
                [
                    'name' => "xth_contribution_contact_last",
                    'label' => "xth Contribution of Contact in the last interval",
                    'class_name' => "CRM_CivirulesAddon_CivirulesConditions_Contribution_xthContributionLast",
                ]
            );
        }
    }

    /**
     * Install extension
     */
    public function install()
    {
        $this->installCiviRuleCondition();
    }
}
