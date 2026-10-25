<?php 
    /** 
      * @name: matrixFunctions 
      * @desc: Functions of schema matrix 
      * @updated: 20/06/10 10:40:56 
      * @version: 1.0 
      */ 
     class MatrixFunctions extends PartModel{ 
        var $db; 
          /** 
            * @name: matrixFunctions 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function __construct($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: create_string_list 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateStringList($filas,$asobjects = false){ 
               $sql = "select  matrix.create_string_list($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number")), $asobjects);
           } 
          /** 
            * @name: echo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function goEcho($arr,$asobjects = false){ 
               $sql = "select  matrix.echo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: extract_boolean_column 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExtractBooleanColumn($arr,$c,$asobjects = false){ 
               $sql = "select  matrix.extract_boolean_column($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($c, "number")), $asobjects);
           } 
          /** 
            * @name: extract_numeric_column 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExtractNumericColumn($arr,$c,$asobjects = false){ 
               $sql = "select  matrix.extract_numeric_column($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($c, "number")), $asobjects);
           } 
          /** 
            * @name: extract_string_column 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExtractStringColumn($arr,$c,$asobjects = false){ 
               $sql = "select  matrix.extract_string_column($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($c, "number")), $asobjects);
           } 
          /** 
            * @name: get_count_of_dims 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetCountOfDims($arr,$asobjects = false){ 
               $sql = "select  matrix.get_count_of_dims($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: get_count_of_value_in 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetCountOfValueIn($arr,$valor,$asobjects = false){ 
               $sql = "select  matrix.get_count_of_value_in($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($valor, "text")), $asobjects);
           } 
          /** 
            * @name: get_max 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMax($arr,$asobjects = false){ 
               $sql = "select  matrix.get_max($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: get_max_pos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMaxPos($arr,$asobjects = false){ 
               $sql = "select  matrix.get_max_pos($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: get_length_of 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetLengthOf($arr,$dimension,$asobjects = false){ 
               $sql = "select  matrix.get_length_of($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($dimension, "number")), $asobjects);
           } 
          /** 
            * @name: get_min_pos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMinPos($arr,$asobjects = false){ 
               $sql = "select  matrix.get_min_pos($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: get_pos_of 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetPosOf($arr,$v,$asobjects = false){ 
               $sql = "select  matrix.get_pos_of($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($v, "text")), $asobjects);
           } 
          /** 
            * @name: set_matrix_in_cube 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function SetMatrixInCube($pcube,$pmatrix,$pos,$asobjects = false){ 
               $sql = "select  matrix.set_matrix_in_cube($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pcube, "text"), PartModel::sqlValue($pmatrix, "text"), PartModel::sqlValue($pos, "number")), $asobjects);
           } 
          /** 
            * @name: create_boolean_matrix 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateBooleanMatrix($filas,$columnas,$asobjects = false){ 
               $sql = "select  matrix.create_boolean_matrix($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number"), PartModel::sqlValue($columnas, "number")), $asobjects);
           } 
          /** 
            * @name: create_numeric_list 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateNumericList($filas,$asobjects = false){ 
               $sql = "select  matrix.create_numeric_list($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number")), $asobjects);
           } 
          /** 
            * @name: get_min 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMin($arr,$asobjects = false){ 
               $sql = "select  matrix.get_min($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text")), $asobjects);
           } 
          /** 
            * @name: create_boolean_cube 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateBooleanCube($x,$y,$z,$asobjects = false){ 
               $sql = "select  matrix.create_boolean_cube($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($x, "number"), PartModel::sqlValue($y, "number"), PartModel::sqlValue($z, "number")), $asobjects);
           } 
          /** 
            * @name: create_string_matrix 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateStringMatrix($filas,$columnas,$asobjects = false){ 
               $sql = "select  matrix.create_string_matrix($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number"), PartModel::sqlValue($columnas, "number")), $asobjects);
           } 
          /** 
            * @name: create_numeric_matrix 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateNumericMatrix($filas,$columnas,$asobjects = false){ 
               $sql = "select  matrix.create_numeric_matrix($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number"), PartModel::sqlValue($columnas, "number")), $asobjects);
           } 
          /** 
            * @name: create_boolean_list 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CreateBooleanList($filas,$asobjects = false){ 
               $sql = "select  matrix.create_boolean_list($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($filas, "number")), $asobjects);
           } 
     } 
