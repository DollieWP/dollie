<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use GFAPI;

/**
 * Class ImportGravityForms
 * @package Dollie\Core\Modules
 */
class ImportGravityForms extends Singleton {

	/**
	 * ImportGravityForms constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init', [ $this, 'import_gravity_form' ] );

	}

	public function import_gravity_form() {
		if ( ! class_exists( 'GFAPI' ) ) {
			return;
		}
		
		$delete_form = wpd_get_dollie_gravity_form_ids( 'dollie-delete' );

		if ( ! $delete_form ) {
			$form = '{"title":"Delete Site","description":"Delete a site","labelPlacement":"top_label","descriptionPlacement":"below","button":{"type":"text","text":"Delete","imageUrl":""},"fields":[{"type":"text","id":1,"label":"Site Name","adminLabel":"","isRequired":true,"size":"medium","errorMessage":"","visibility":"visible","inputs":null,"formId":18,"description":"Please type the name of the site to confirm deletion, this can not be undone.","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputType":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","choices":"","conditionalLogic":"","productField":"","enablePasswordInput":"","maxLength":"","multipleFiles":false,"maxFiles":"","calculationFormula":"","calculationRounding":"","enableCalculation":"","disableQuantity":false,"displayAllCategories":false,"useRichTextEditor":false,"pageNumber":1,"displayOnly":""},{"type":"hidden","id":2,"label":"dollie-delete","adminLabel":"","isRequired":false,"size":"medium","errorMessage":"","visibility":"visible","inputs":null,"formId":18,"description":"","allowsPrepopulate":false,"inputMask":false,"inputMaskValue":"","inputType":"","labelPlacement":"","descriptionPlacement":"","subLabelPlacement":"","placeholder":"","cssClass":"","inputName":"","noDuplicates":false,"defaultValue":"","choices":"","conditionalLogic":"","productField":"","multipleFiles":false,"maxFiles":"","calculationFormula":"","calculationRounding":"","enableCalculation":"","disableQuantity":false,"displayAllCategories":false,"useRichTextEditor":false,"pageNumber":1,"displayOnly":""}],"version":"2.3.0.2","id":18,"useCurrentUserAsAuthor":true,"postContentTemplateEnabled":false,"postTitleTemplateEnabled":false,"postTitleTemplate":"","postContentTemplate":"","lastPageButton":null,"pagination":null,"firstPageCssClass":null,"notifications":{"5dc1035a71a68":{"id":"5dc1035a71a68","to":"{admin_email}","name":"Admin Notification","event":"form_submission","toType":"email","subject":"New submission from {form_title}","message":"{all_fields}"}},"confirmations":{"5dc1035a77fe3":{"id":"5dc1035a77fe3","name":"Default Confirmation","isDefault":true,"type":"message","message":"The given site has been submitted for deletion.","url":"","pageId":0,"queryString":"","disableAutoformat":false,"conditionalLogic":[]}},"subLabelPlacement":"below","cssClass":"","enableHoneypot":false,"enableAnimation":false,"save":{"enabled":false,"button":{"type":"link","text":"Save and Continue Later"}},"limitEntries":false,"limitEntriesCount":"","limitEntriesPeriod":"","limitEntriesMessage":"","scheduleForm":false,"scheduleStart":"","scheduleStartHour":"","scheduleStartMinute":"","scheduleStartAmpm":"","scheduleEnd":"","scheduleEndHour":"","scheduleEndMinute":"","scheduleEndAmpm":"","schedulePendingMessage":"","scheduleMessage":"","requireLogin":true,"requireLoginMessage":"","is_active":"1","date_created":"2019-11-05 05:06:34","is_trash":"0"}';

			$form   = json_decode( $form, true );
			$result = GFAPI::add_form( $form );
		}
	}

}
