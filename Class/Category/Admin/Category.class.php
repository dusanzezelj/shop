<?php
namespace category\admin;
require_once 'C:\wamp\www\Shop\Class\Category\Shop\Category.class.php';

class Category extends \categoty\shop\Category
{
    private $default_lang;
    private $active = array(0, 1);
  public function __construct($db) 
  {
      parent::__construct($db);
      $this->defaultLanguage();
  }
  private function defaultLanguage()
  {
      $query="SELECT ID FROM language WHERE IsDefault = 1";
      $result = $this->db->query($query);
      $id = $this->db->fetchArray($result);
      $this->default_lang = $id['ID'];
  }
  private function isValidContent(&$names)
  {
      if($names['IDLanguage']){
          parent::isValidLanguage($names['IDLanguage']);
      } else {
        $names['IDLanguage'] = $this->default_lang;  
      }
      if(empty($names['content'])){
          throw new \Exception();
      }
  }
  public function add($names=array(), $descriptions=array(), $meta_descriptions=array(), 
                      $id_parent=0, $id_avatar=0, $sort_order=0, $is_active=1)
  {
      try{
          $this->isValidContent($names);         
         } catch (\Exception $e){ 
             throw new \Exception ("Please provide at least name of category on default language", 1007);             
         }
         try{
          $this->isValidContent($descriptions);         
         } catch (\Exception $e){ 
             throw new \Exception ("Please provide at least description of category on default language", 1008);             
         }
         try{
          $this->isValidContent($meta_descriptions);         
         } catch (\Exception $e){ 
             throw new \Exception ("Please provide at least meta description of category on default language", 1009);             
         }
         $this->isValidIdParent($id_parent);
         $this->isValidAvatar($id_avatar);
         $this->isValidSortOrder($sort_order);
         $this->isActiveFlagValid($is_active);
         $this->isValidName($names['content'], $id_parent);
         if($names['IDLanguage'] == $descriptions['IDLanguage'] && 
             $names['IDLanguage'] == $meta_descriptions['IDLanguage']){
             $lang =  $names['IDLanguage'];
             $query = "INSERT INTO 
                 category (IDParent, IDAvatar, SortOrder, ISActive)
                  VALUES
                   ($id_parent, $id_avatar, $sort_order, $is_active)";
             $result = $this->db->query($query);
             $id_category = $this->db->getLastInsertID();
             $query1 = "INSERT INTO 
                 categorydetail (IDCategory, IDLanguage, Name, Description, MetaDescription) 
                 VALUES 
                 ($id_category, $lang, '".$names['content']."', '".$description['content']."', '".$meta_descriptions['content']."')";
             $result1 = $this->db->query($query1);
             return $this->db->getLastInsertID();
         } else {
             echo 'ID Language are not the same';
             return -1;
         }
  }
  private function isValidAvatar($id_avatar)
  {
      $query = "SELECT IDAvatar FROM category WHERE IDAvatar = $id_avatar";
      if ($this->db->getNumRows($query) < 1){
            throw new \Exception("Please provide a valid IDAvatar or 0", 1010); 
        }
  }
  private function isValidSortOrder($sort_order)
  {
      $query = "SELECT SortOrder FROM category WHERE SortOrder = $sort_order";
      if ($this->db->getNumRows($query) < 1){
            throw new \Exception("Please provide a valid SortOrder", 1011); 
        }
  }
  private function isActiveFlagValid($is_active) 
  {
           if(!in_array($is_active, $this->active)){
               throw new \Exception("Please provide a valid IsActive flag (0 or 1)", 1012);
           }
  }
  private function isValidName($name, $id_parent)
  {
      $query="SELECT 
              c.IDParent, cd.Name 
              FROM 
              category as c 
              INNER JOIN  
              categorydetail as cd 
              ON 
              (c.ID = cd.IDCategory) 
              WHERE 
              c.IDParent= $id_parent 
              AND 
              cd.Name = '$name'";
      if ($this->db->getNumRows($query) > 0){
            throw new \Exception("Category with same name, at the same level, already exist", 1013); 
        }
  }
  public function ChangeSortOrder($id=0, $sort_order=0)
  {
      $this->isValidId($id);
      $this->isValidSortOrder($sort_order);
      $query = "SELECT ID FROM category
          WHERE ID = $id AND SortOrder = $sort_order";
      if ($this->db->getNumRows($query) > 0){
            throw new \Exception("Nothing is changed", 1014); 
        } else {
          $update_query = "UPDATE category 
                           SET SortOrder = $sort_order
                           WHERE ID = $id";
          if($result = $this->db->query($update_query)){
              return true;
          } else {
              return false;  
          }
        }
  }
  private function activeCheck($id, $active)
  {
       $query= "SELECT ID FROM category
               WHERE ID = $id AND IsActive = $active";
       if ($this->db->getNumRows($query) > 0){
            throw new \Exception("Nothing is changed", 1014); 
       }
  }

  public function activate($id=0)
  {
      $this->isValidId($id);
      $this->activeCheck($id, 1);     
      $update_query = "UPDATE category 
                       SET IsActive = 1
                       WHERE ID = $id";
          if($result = $this->db->query($update_query)){
              return true;
          } else {
              return false;  
          }           
  }
  public function deActivate($id=0)
  {
      $this->isValidId($id);
      $this->activeCheck($id, 0);
      $update_query = "UPDATE category 
                       SET IsActive = 0
                       WHERE ID = $id";
          if($result = $this->db->query($update_query)){
              return true;
          } else {
              return false;  
          }          
  }
  private function isCategoryHaveProduct($id)
  {
      $query = "SELECT ID FROM productcategory
                WHERE IDCategory = $id";
      return $this->db->getNumRows($query) > 0 ? true : false;       
      
  }

  public function delete($id=0, $unlink_products=false)
  {
      $this->isValidId($id);
      $cat_have_products = $this->isCategoryHaveProduct($id);
      if($unlink_products == false && $cat_have_products == true){
          throw new Exception("Category can't be deleted because it contain products. Try with UnlinkProducts=True", 1015);
  }
      if($cat_have_products){
          $unlink_query = "UPDATE productcategory
                           SET IDCategory = 0
                           WHERE IDCategory = $id";
          $this->db->query($unlink_query);
          $affected_rows = $this->db->getAffectedRows();
          $delete_query = "UPDATE category
                           SET IsActive = -1
                           WHERE ID = $id";
          $this->db->query($delete_query);
          return $affected_rows;
      } elseif (!$cat_have_products) {
          $delete_query = "UPDATE category
                           SET IsActive = -1
                           WHERE ID = $id";
          if($this->db->query($delete_query)){
              return true;
          } else {
              return false;
          }
      }
      
  }
}

?>
