<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class Initial extends AbstractMigration {

  /**
   * Change Method.
   *
   * Write your reversible migrations using this method.
   *
   * More information on writing migrations is available here:
   * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
   *
   * The following commands can be used in this method and Phinx will
   * automatically reverse them when rolling back:
   *
   *    createTable
   *    renameTable
   *    addColumn
   *    addCustomColumn
   *    renameColumn
   *    addIndex
   *    addForeignKey
   *
   * Any other destructive changes will result in an error when trying to
   * rollback the migration.
   *
   * Remember to call "create()" or "update()" and NOT "save()" when working
   * with the Table class.
   */
  public function change () {


    // User

    $this->table ('user', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '使用者'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // Basic fields
      ->addColumn ('name', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '名稱'])
      ->addColumn ('code', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '認證碼'])

      // Line 資料
      ->addColumn ('line_id', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '使用者鍵值'])
      ->addColumn ('line_nick', 'char', ['limit' => 32, 'null' => TRUE, 'comment' => '使用者暱稱'])
      ->addColumn ('line_avatar', 'char', ['limit' => 128, 'null' => TRUE, 'comment' => '使用者頭像'])

      // Timestamp
      ->addColumn ('createdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'comment' => '建立日期'])

      // Keys
      ->addIndex ('line_id')

      // Mutiple combined keys: empty
      ->create ();


    // Team

    $this->table ('team', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '團隊'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // Basic fields
      ->addColumn ('name', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '名稱'])
      ->addColumn ('code', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '認證碼'])

      // Line 群組資料
      ->addColumn ('line_group_id', 'char', ['limit' => 64, 'null' => TRUE, 'comment' => '群組鍵值'])
      ->addColumn ('line_group_name', 'char', ['limit' => 32, 'null' => TRUE, 'comment' => '群組名稱'])
      ->addColumn ('line_group_desc', 'char', ['limit' => 128, 'null' => TRUE, 'comment' => '群組頭像'])

      // Timestamp
      ->addColumn ('createdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'comment' => '建立日期'])

      // Keys
      ->addIndex ('line_group_id')

      // Mutiple combined keys: empty
      ->create ();


    // Team user

    $this->table ('team_user', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '團隊使用者關聯'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // 群組 / 使用者資料
      ->addColumn ('type', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => FALSE, 'comment' => '所屬類型'])
      ->addColumn ('team_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '團隊鍵值'])
      ->addColumn ('user_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '使用者鍵值'])

      // Keys
      ->addIndex ('type')
      ->addIndex ('team_id')
      ->addIndex ('user_id')

      // Mutiple combined keys: empty
      ->addIndex (['type', 'team_id'])
      ->addIndex (['type', 'user_id'])
      ->create ();


    // Issue

    $this->table ('issue', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '事項'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // 群組 / 使用者資料
      ->addColumn ('team_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '團隊鍵值'])
      ->addColumn ('user_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'null' => TRUE, 'comment' => '被指派者鍵值'])
      ->addColumn ('creator_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '建立者者鍵值'])

      // 標題
      ->addColumn ('title', 'char', ['limit' => 128, 'null' => FALSE, 'comment' => '標題'])
      ->addColumn ('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => FALSE, 'default' => 0, 'comment' => '狀態'])

      // Timestamp
      ->addColumn ('duedate', 'timestamp', ['null' => TRUE, 'comment' => '到期日期'])
      ->addColumn ('createdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'comment' => '建立日期'])

      // Keys
      ->addIndex ('team_id')
      ->addIndex ('user_id')
      ->addIndex ('creator_id')
      ->addIndex ('duedate')

      // Mutiple combined keys: empty
      ->create ();


    // Tag

    $this->table ('tag', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '標籤'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // 群組 / 使用者資料
      ->addColumn ('team_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '團隊鍵值'])

      // 標題
      ->addColumn ('name', 'char', ['limit' => 128, 'null' => FALSE, 'comment' => '名稱'])

      // Timestamp
      ->addColumn ('createdate', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'comment' => '建立日期'])

      // Keys
      ->addIndex ('team_id')

      // Mutiple combined keys: empty
      ->create ();


    // Issue tag

    $this->table ('issue_tag', ['id' => FALSE, 'primary_key' => 'id', 'comment' => '事項標籤關聯'])
      ->addColumn ('id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'identity' => TRUE])

      // 群組 / 使用者資料
      ->addColumn ('task_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '任務鍵值'])
      ->addColumn ('tag_id', 'integer', ['limit' => MysqlAdapter::INT_BIG, 'signed' => FALSE, 'comment' => '標籤鍵值'])

      // Keys
      ->addIndex ('task_id')
      ->addIndex ('tag_id')

      // Mutiple combined keys: empty
      ->create ();

  }
}
