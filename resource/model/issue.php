<?php


  /**
   *
   * Issue model
   *
   */
  class Issue extends \Model {

    public static $_table     = 'issue';
    public static $_id_column = 'id';

    public function team () {
      return $this->belongs_to ('Team');
    }

    public function assignee () {
      return \Model::Factory ('User')->where ('id', $this->assignee_id);
    }

    public function opener () {
      return \Model::Factory ('User')->where ('id', $this->opener_id);
    }
  }
