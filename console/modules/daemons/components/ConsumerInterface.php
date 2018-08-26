<?php
	/**
	 * Created by PhpStorm.
	 * User: Konstantin Silukov
	 * Date: 26.08.2018
	 * Time: 17:25
	 */

	namespace console\modules\daemons\components;

	/**
	 * interface with methods which must be realizing in consumer classes
	 * */
	interface ConsumerInterface {
		public function getData();
		public function saveData();
	}