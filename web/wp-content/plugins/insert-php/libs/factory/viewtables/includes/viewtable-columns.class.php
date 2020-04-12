<?php

	class FactoryViewtables410_Columns {

		public $isClearn = false;
		public $columns;

		public function clear()
		{
			$this->isClearn = true;
			$this->columns = array();
		}

		public function add($id, $title)
		{

			$this->columns[] = array(
				'id' => $id,
				'title' => $title
			);
		}

		public function getAll()
		{
			return $this->columns;
		}
	}