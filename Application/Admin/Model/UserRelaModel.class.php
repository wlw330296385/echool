<?php 

namespace Home\Model;
use Think\Model\RelationModel;
class UserModel extends RelationModel{
    protected $_link = array(        
		    'Profile'=>array(            
		    'mapping_type'      => self::HAS_ONE,            
		    'class_name'        => 'vip', 
		    'mapping_name'      =>  'vip'      
		    ),        
    );
}

?>