<?
    #Name: WebFacer Databsewrapper
    #version: 1.8.3
    #Author: Ilic Davor
    #website: http://webfacer.com
    #license under Creative Commons see: http://creativecommons.org/licenses/by-nc-nd/3.0/
    #last update: 10-11-2011 20:15


    //Notice: Variable need to be cleaned up, which i do not need anymore!
    //Changelog: Added to Github
    

    class WF_DB {
        
        private $host		    = "localhost";
        
        private $dbuser;
        
        private $dbpass;
        
        private $dbname;
        
        private $pconnect	    = false;//Persistente Verbindung wenn false wird keine persistente Verbindung aufgebaut
        
        private static $connected   = false;
        
        private static $instance;
        
        private $tablename;
        
        private $error;
        
        private $query;
        
        private $countquerys;
        
        private $sqlstatement;
        
        private $statement;
        
        private $result;
        
        private $escapedString;
        
        
        
        
        //Konstruktor: soll nicht von aussen genutzt werden da ich den Singelto Pattern nutze
        private function __construct($host,$dbuser,$dbpass,$dbname,$pconnect) {
            
            $this->host		= $host;
            
            $this->dbuser	= $dbuser;
            
            $this->dbpass	= $dbpass;
            
            $this->dbname	= $dbname;
            
            $this->pconnect     = $pconnect;
            
            $this->tablename    = $tablename;
            
            $this->connected    = $connected;
            
            $this->error        = $error;
            
            $this->query        = $query;
            
            $this->countquery   = $countquery;
            
            $this->sqlstatement = $sqlstatement;
            
            $this->result       = $result;
        }
        
        public static function connect($host=null,$dbuser=null,$dbpass=null,$dbname=null,$pconnect=false) {
            if(!isset(self::$instance)) {
                $class = __CLASS__;
                self::$instance = new $class($host,$dbuser,$dbpass,$dbname,$pconnect);
            }
            
            return self::$instance;
        }
        
        
        // Halte Benutzer vom Klonen der Instanz ab
        public function __clone() {
            trigger_error('Klonen ist nicht erlaubt.', E_USER_ERROR);
        }
        
        //Connection zwischen der DB und server
        private function connection() {
            if($this->pconnect==false) {
                self::$connected = @mysql_connect($this->host,$this->dbuser,$this->dbpass);
            }else{
                self::$connected = @mysql_pconnect($this->host,$this->dbuser,$this->dbpass);
            }
            
            if(!self::$connected) {
                
                return $this->error('OPENCON');
                
            }else{
            
                if(!@mysql_select_db($this->dbname,self::$connected)) {
                    
                    return $this->error("OPENDB");
                
                }else{
                
                    return $this->connected = true;
                    
                }
            }
            
        }
        
        //Uebergibt die anfrage des Clients an query weiter
        public function run($sql,$action,$numb=false) {
            $this->sqlstatement = $sql;//mann sollte den string im statement escapen bevor man diesen insertet
            
            switch($action) {
                case 'read':
                    return $this->work(true);
                break;
                
                case 'changes':
                    return $this->work(false);
                break;
                
                default:
                    die('read() braucht eine aktion um arbeiten zu k&ouml;nnen!');                
                break;
            }
        }
        
        #-############################################# 
        # desc: does an select query with an array but you can also choose the object query
        # param: statement, table, assoc array with param (doesn't need escaped) param can be null for * or empty, where condition 
        # returns: 
        public function select($table, $param=null, $result='assoc') {//result wird noch nicht ausgefÅ¸hrt derzeit wird nur 
            $this->q = $this->setStatementAction('select',$table,$param);
            return $this->run($this->q,'read');
        }
        
        public function insert($table, $param) {
            $this->q = $this->setStatementAction('insert',$table,$param);
            return $this->run($this->q,'changes');
        }
        
        public function update($table, $param) {
            $this->q = $this->setStatementAction('update',$table,$param);
            return $this->run($this->q,'changes');
        }
        
        public function delete($table, $param) {
            $this->q = $this->setStatementAction('delete',$table,$param);
            return $this->run($this->q,'changes');
        }
        
        public function queryDebugger() {
            
        }
        
        #-############################################# 
        # desc: 
        # param: 
        # returns:
        private function setStatementAction($statement,$table,$param) {
            $n='';
            $v='';
            $u='';
            $w='';
            $o='';
            $l='';
            $this->statement = $statement;
            $statement = strtolower($statement);
            
            if($param!=null) {
                foreach($param as $key=>$value) {                    
                    //mit if condition
                    if(strtoupper($key)!='WHERE' && strtoupper($key)!='LIMIT' && 'ORDER'!=strtoupper($key)) {
                        if($statement=='insert'||$statement=='update') {
                                if($statement=='update') {
                                    $u.= $key.'=\''.$value.'\',';
                                }
                                if($statement=='insert') {
                                    $n.= '`'.$key.'`,';
                                    
                                    $v.= '\''.$value.'\',';                    
                                
                                }
                        }elseif($statement=='select'){
                            if(!empty($value)) {
                                $n.= '`'.$this->escape($value).'`,';
                            }
                        }
                    }
                    if($statement!='insert') {
                        if('WHERE'==strtoupper($key)) {
                            foreach($value as $clause=>$criterion) {
                                $w.= $clause."'".$criterion."'";
                            }
                        }
                        if($statement=='select') {
                            if('ORDER'==strtoupper($key)) {
                                foreach($value as $by=>$asdesc) {
                                    $o.= $asdesc.',';
                                }
                            }
                            if('LIMIT'==strtoupper($key)) {
                                $l= '';
                                foreach($value as $number) {
                                    $l.=$number.',';
                                }
                            }
                        }
                    }
                }
            }
            
            
            $n=rtrim($n,',');
            $n=empty($n)?$n='*':$n;
            if(empty($v) || $param==null) $v= '*'; else $v=rtrim($v,',');
            $u=rtrim($u,',');
            
            if(!empty($w)) $where=' WHERE '.$w; else $where=' WHERE 1';
            if(!empty($o)) $order=' ORDER BY '.rtrim($o,',');
            $limit=empty($l)?'':' LIMIT '.rtrim($l,',');
            
            
            
            switch($statement) {
                   case 'insert':
                    $q= 'INSERT INTO '.$table.' ('.$n.') VALUES ('.$v.')';
                    break;
                case 'update':
                    $q= 'UPDATE `'.$table.'` SET '.$u.$where;
                    break;
                case 'select':
                    $q= 'SELECT '.$n.' FROM '.$table.$where.$order.$limit;
                    #print($q);
                    break;
                case 'delete':
                    $q= 'DELETE FROM '.$table.$where;
                    break;
            }
            
            return $q;
        }
        
        
        // causes error because canÂ´t connect to DataBase
        private function escape($string) {
            if(get_magic_quotes_runtime()) $string = stripslashes($string);
            
            $escaped = stripslashes($string); ;//@mysql_real_escape_string($string); this line causes error info @ me
            
            return $escaped;
        }
        
        public function query() {
            $query = @mysql_query($this->sqlstatement);
            #print_r($this->sqlstatement);
            if(!$query) {
                return $this->error('QUERY');
            }else{
                return $this->query = $query;
            }            
        }
        
        //Datenausgabe daten aus DB
        private function work($dowork) {
            
            if($this->connection()===true) {
                
                if($dowork==true) {
                    if($this->query()==true) {
                    
                            $i = 0;
                            while($row = @mysql_fetch_object($this->query)) {
                                $result[$i]=$row;
                                $i++;
                            }
                            unset($i);
                        
                        mysql_free_result($this->query);
                        
                        return $result;
                        
                    }else{
                    
                        return $this->error('QUERY');
                    
                    }
                }else{
                    return $this->query();
                }
                
            }else{
            
                return $this->error('OPENCON');
                
            }
            
        }
        
        //Zaehlt angesprochene datensaetze
        public function countquery() {
            #echo $this->statement;
            if($this->statement=='select') {
                return mysql_num_rows($this->query());
            }else{
                return flase;
            }
        }
        
        private function affected_rows() {
            $afrows = mysql_affected_rows($this->query());
            
            return $afrows;
        }
        
        /*private function clearstorage() {
        
            return @mysql_free_result($this->query());
        
        }*/
        
        public function close() {
            if(!isset(self::$connected)) return;
            mysql_close(self::$connected);
        }
        
        // !Error
        private function error($errorstatement) {
            
            switch($errorstatement) {
                
                case 'OPENCON':
                    $error = "Connection has failed to Database!";
                    break;
                
                case 'OPENDB':
                    $error = "Databasename is not the right one!";
                    break;
                
                case 'QUERY':
                    $error = "Query has failed!";
                    break;
                
                case 'FETCH':
                    $error = "FETCH OBJECT has failed!<br />".$this->sqlstatement;
                    break;
                
            }
            
            print $error."<br />";
            echo $this->sqlstatement.'<br />';
            die(mysql_error());
            
        }
    }

?>