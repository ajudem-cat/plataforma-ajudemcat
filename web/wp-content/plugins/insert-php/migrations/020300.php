<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WINPUpdate020300 extends Wbcr_Factory422_Update {

	public function install() {
		$this->plugin->updatePopulateOption( "last_check_premium_update_time", 0 );
	}
}