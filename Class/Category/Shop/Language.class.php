<?php
namespace category\shop;

require_once __DIR__.'/../Database.php';

class Language 
{
   private $id;
   private $name;
   private $code;
   private $is_default;
   private $is_active;
   private $db;
   public function __construct($db)
   {
       $this->db=$db;
   }
  public function isValid($id)
  {
      $query= "select * from language where ID = '$id' and IsActive = '1'";
      return $this->db->getNumRows($query) > 0 ? true : false ;
      
  }
}

?>
