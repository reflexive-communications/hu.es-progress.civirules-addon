<?php
/**
 * Class for CiviRules Condition xth Contribution in the last <interval> Form
 *
 * @author Sandor Semsey <semseysandor@gmail.com>
 * @date 04 Jun 2020
 * @license AGPL-3.0
 */
class CRM_CivirulesAddon_CivirulesConditions_Form_Contribution_xthContributionLast extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'operator', ts('Operator'), CRM_Civirules_Utils::getGenericComparisonOperatorOptions(), TRUE);
    $this->add('select', 'financial_type', ts('of Financial Type(s)'), CRM_Civirules_Utils::getFinancialTypes(), TRUE,
      array('id' => 'financial_type_ids', 'multiple' => 'multiple','class' => 'crm-select2'));
    $this->add('text', 'number_contributions', ts('Number of Contributions'), array(), TRUE);
    $this->addRule('number_contributions','Number of Contributions must be a whole number','numeric');
    $this->addRule('number_contributions','Number of Contributions must be a whole number','nopunctuation');
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    $this->add('text', 'interval', ts('in the last'), array(), true);
    $this->add('select','interval_unit','interval',CRM_CivirulesAddon_CivirulesConditions_Contribution_xthContributionLast::INTERVAL_UNITS,true);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);
    if (!empty($data['number_contributions'])) {
      $defaultValues['number_contributions'] = $data['number_contributions'];
    }
    if (!empty($data['financial_type'])) {
      $defaultValues['financial_type'] = $data['financial_type'];
    }
    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    if (!empty($data['interval'])) {
      $defaultValues['interval'] = $data['interval'];
    }
    if (!empty($data['interval_unit'])) {
      $defaultValues['interval_unit'] = $data['interval_unit'];
    }
    return $defaultValues;
  }

  /**
   * Function to add validation condition rules (overrides parent function)
   *
   * @access public
   */
  public function addRules() {
    $this->addFormRule(array('CRM_CivirulesConditions_Form_Contribution_xthContribution', 'validateCompareZero'));
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['interval'] = $this->_submitValues['interval'];
    $data['interval_unit'] = $this->_submitValues['interval_unit'];
    $data['number_contributions'] = $this->_submitValues['number_contributions'];
    $data['operator'] = $this->_submitValues['operator'];
    $data['financial_type'] = $this->_submitValues['financial_type'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}
