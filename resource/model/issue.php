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

    public function user () {
      return $this->belongs_to ('User');
    }

    public function creator () {
      return \Model::Factory ('User')->where ('id', $this->creator_id);
    }
  }
