<?php
namespace shop\category;
require_once __DIR__.'/../Database.php';
require_once 'Language.class.php';

class Category 
{
    protected $db;
    private $id;
    private $id_parent;
    private $id_avatar;
    private $sort_order;
    private $is_active;
    
    public function __construct($db)
    {
        $this->db=$db;
    }
    public function isValidLanguage($id)
    {
        $lang= new Language($this->db);
        if (!$lang->isValid($id)){
            throw new \Exception("Please provide a valid IDLanguage", 1001);
        }
    }
    public function isValidIdParent($id)
    {
        $query = "select ID from category where IDParent = '$id'";
        if ($this->db->getNumRows($query) == 0){
            throw new \Exception("Please provide a valid IDParent or 0", 1002);
        }
    }
    public function isSortFlagValid($sort)
    {
        if ($sort != 'DEFAULT' && $sort != 'PRODUCTS' && $sort != 'ALPHABETIC'){
            throw new \Exception("Please provide a valid Sort flag ('DEFAULT', 'PRODUCTS' or 'ALPHABETIC')", 1003);
        }
    }
    public function isActiveFlagValid($flag) 
    {
        if ($flag != 0 && $flag != 1 && $flag != 2){
            throw new \Exception("Please provide a valid Active flag (0, 1 or 2)", 1004);
        }
    }
    public function isChildrenFlagValid($flag) 
    {
        if ($flag != 0 && $flag != 1){
            throw new \Exception("Please provide a valid Active flag (0 or 1)", 1005);
        }
    }
    public function isValidId($id)
    {
        $query="select * from categorydetail where ID = '$id'";
        if ($this->db->getNumRows($query) == 0) {
            throw new \Exception("Please provide a valid ID of category", 1006);
        }
    }

    public function getCategoriesWithChildren($id_language=0, $id_parent=0, $sort='DEFAULT', $active=1)
    {
        $tree= array();
        $query="select c.ID, c.IDParent, cd.Name from category as c, categorydetail as cd";
        $query.=" where c.ID = cd.IDCategory and c.IDParent= '$id_parent' and cd.IDLanguage = '$id_language'";
        $query.=" and c.IsActive = '$active'";
        $result= $this->db->query($query);                
        while($row = $this->db->fetchArray($result)){
                 $tree[] = array("ID" => $row[0], "Name" => $row[2], "IDParent" => $row[1],
                "Children" => $this->getCategoriesWithChildren($id_language, $row[0], $sort, $active));
         }
            return $tree;
    }
    public function getCategoriesNoChildren($id_language=0, $id_parent=0, $sort='DEFAULT', $active=1)
    {
        $query="select c.ID, c.IDParent, cd.Name from category as c, categorydetail as cd";
        $query.=" where c.ID = cd.IDCategory and c.IDParent= '$id_parent' and cd.IDLanguage = '$id_language'";
        $query.=" and c.IsActive = '$active'";
        $arr= array();
        $result= $this->db->query($query);                
        while($row = $this->db->fetchArray($result)){
            $arr[]= $row;
        }
        return $arr;
    }
    public function getCategories($id_language=0, $id_parent=0, $sort='DEFAULT', $active=1, $children=1)
    {      
        $this->isValidLanguage($id_language);
        $this->isValidIdParent($id_parent);
        $this->isSortFlagValid($sort);
        $this->isActiveFlagValid($active);
        $this->isChildrenFlagValid($children);
        if ($children == 1) {
            $result = $this->getCategoriesWithChildren($id_language, $id_parent, $sort, $active);
            return $result;
        } elseif ($children == 0) {
            return $this->getCategoriesNoChildren($id_language, $id_parent, $sort, $active);       
        }
    }   
    public function getCategory($id_language=0, $id=0)
    {
        $this->isValidLanguage($id_language);
        $this->isValidId($id);
        $query="select * from categorydetail where ID = '$id' and IDLanguage = '$id_language'";
        $result= $this->db->query($query);
        return $row = $this->db->fetchArray($result);
    }
}
$category= new Category($db);
$result= $category->getCategoriesWithChildren(1);
$cat= $category->getCategory(1,2);
echo '<pre>';
print_r($cat);
//print_r($result);


?>
