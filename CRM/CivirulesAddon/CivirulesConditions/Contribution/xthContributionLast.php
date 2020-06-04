<?php
/**
 * Class for CiviRule Condition xth Contribution in the last time interval
 *
 * @author Sandor Semsey <semseysandor@gmail.org>
 * @date 04 Jun 2020
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesAddon_CivirulesConditions_Contribution_xthContributionLast extends CRM_CivirulesConditions_Contribution_xthContribution {

  /**
   * Time interval units
   */
  public const INTERVAL_UNITS=['days','months','years'];

  /**
   * Condition parameters
   *
   * @var array
   */
  protected $_conditionParams = [];

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->_conditionParams = [];
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->_conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method is mandatory and checks if the condition is met
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contactId = $triggerData->getContactId();
    // count number of contributions of financial types for contact
    try {
      // Get interval from trigger
      $interval=$this->_conditionParams['interval'];
      $interval_unit=self::INTERVAL_UNITS[$this->_conditionParams['interval_unit']];

      // Make time interval string
      $interval_str="-{$interval} {$interval_unit}";

      // Calculate date
      $date_after=date('Y-m-d',strtotime($interval_str));

      $apiParams = [
        'financial_type_id' => ['IN' => $this->_conditionParams['financial_type']],
        'contact_id' => $contactId,
        'contribution_status_id' => "Completed",
        'receive_date' => ['>=' => $date_after],
      ];
      $count = (int) civicrm_api3('Contribution', 'getcount', $apiParams);
      switch ($this->_conditionParams['operator']) {
        // equals
        case 0:
          if ($count == $this->_conditionParams['number_contributions']) {
            return TRUE;
          }
          break;
        // greater than
        case 1:
          if ($count > $this->_conditionParams['number_contributions']) {
            return TRUE;
          }
          break;
        // greater than or equal
        case 2:
          if ($count >= $this->_conditionParams['number_contributions']) {
            return TRUE;
          }
          break;
        // less than
        case 3:
          if ($count < $this->_conditionParams['number_contributions']) {
            return TRUE;
          }
          break;
        // less than or equal
        case 4:
          if ($count <= $this->_conditionParams['number_contributions']) {
            return TRUE;
          }
          break;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      Civi::log()->error(ts('Unexpected error from API Contribution getcount in ') . __METHOD__
        . ts(', error message: ') . $ex->getMessage());
    }
    return FALSE;
  }

  /**
   * Method is mandatory, in this case no additional data input is required
   * so it returns FALSE
   *
   * @param int $ruleConditionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/contribution/xthcontributionlast/', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Overridden parent method to set user friendly condition text in form
   *
   * @return string
   */
  public function userFriendlyConditionParams() {
    $operators = CRM_Civirules_Utils::getGenericComparisonOperatorOptions();
    $financialTypes = CRM_Civirules_Utils::getFinancialTypes();
    $finTypesTxt = array();
    foreach ($this->_conditionParams['financial_type'] as $financialType) {
      $finTypesTxt[] = $financialTypes[$financialType];
    }
    $intervals=self::INTERVAL_UNITS;
    return ts('Number of contributions in the last ').
      $this->_conditionParams['interval'].' '.$intervals[$this->_conditionParams['interval_unit']].' of financial type ' . implode(' or ', $finTypesTxt)
      . ' ' .  $operators[$this->_conditionParams['operator']] . ' '
      . $this->_conditionParams['number_contributions'];
  }
}
