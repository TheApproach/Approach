<?php

interface Permission{
  public function create($path);
  public function read($path);
  public function write($path, $content, $mode);
  public function run($path);
  public function delete($path);
}

trait DenyUser{
  private static function deny_user($operation, $path='/'){ 
      trigger_error(
          '"'.$operation.'" operation not permitted on ' . $path . PHP_EOL, 
         E_USER_WARNING
      );
    return false;
  }
  public function create($path){   return self::deny_user('create'); }
  public function read($path){      return self::deny_user('read');    }
  public function run($path){        return self::deny_user('run');      }
  public function delete($path){   return self::deny_user('delete'); }
  public function write($path, $content, $mode){ 
      return self::deny_user('write');
  }
}
trait CreatePermission{
  public function create($path){ /* create resource */ }
}
trait ReadPermission{
  public function read($path){ /* read resource */ }
}
trait RunPermission{
  public function run($path){ /* execute resource */ }
}
trait DeletePermission{
  public function delete($path){ /* delete resource */ }
}
trait WritePermission{
  public function write($path, $content, $mode){ /* write to resource */ }
}

trait Authority{
	private static function allowed(Permission $permitType) : Permission{
		$issued_permit = new NoPermit();
		$allowed = false; 

		// however you check for permissions, do that here. 
		// set allowed to true if that permission is allowed
		
		if($allowed){	
			$issued_permit = new $permitType();
		}
		return $issued_permit;
	}
}


class NoPermit implements Permission{  use DenyUser;  }
class CreatePermit extends NoPermit{  use CreatePermission;   }
class ReadPermit extends NoPermit{  use ReadPermission;   }
class RunPermit extends NoPermit{  use RunPermission;   }
class DeletePermit extends NoPermit{  use DeletePermission;   }
class WritePermit extends NoPermit{  use WritePermission;   }

class AccessPermit extends NoPermit{
	use ReadPermission;
	use RunPermission;
}
class AuthorPermit extends NoPermit{
  use ReadPermission;
  use WritePermission;
}
class SuperPermit extends NoPermit{
  use CreatePermission;
  use ReadPermission;
  use WritePermission;
  use DeletePermission;
}
class AdminPermit{
  use CreatePermission;
  use ReadPermission;
  use WritePermission;
  use DeletePermission;
  use RunPermission;
}


class User{
	use Authority;
}
