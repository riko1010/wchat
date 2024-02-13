<?php
namespace WChat;
class Request
{
  private $StatusConsole;
  private $Data;
  private $Writeable;

  public function __construct($Requests, $Writeable = false)
  {
    $data = [];
    $filters = [];
    $this->Writeable = $Writeable;
    foreach ($Requests as $Request => $Val) {
      $data[$Request] = $Val;
      $filters[$Request] = 'trim|empty_string_to_null|strip_tags|escape';
    }

    $RequestData = new \Elegant\Sanitizer\Sanitizer($data, $filters);
    $RequestData = $RequestData->sanitize();

    $this->Data = $RequestData;
  }

  public function __get($PropertyName)
  {
    try {
      return $this->Data[$PropertyName];
    } catch (\Exception | \Throwable $e) {
      throw new \ErrorException($PropertyName . ' is not in $_REQUEST', 0, 8);
    }
  }

  public function __set($PropertyName, $Val)
  {
    if (!$this->Writeable) {
      throw new \Exception(
        $PropertyName .
          ' is not writeable. Pass true as the second parameter in ' .
          get_class($this) .
          ' to write temporarily.'
      );
      return false;
    }

    $this->Data[$PropertyName] = $Val;
  }

  public function __unset($PropertyName)
  {
    try {
      unset($this->Data[$PropertyName]);
    } catch (\Exception | \Throwable $e) {
    }
  }

  public function __debugInfo()
  {
    return $this->Data;
  }
  
  public function __isset($PropertyName){
    return isset($this->Data[$PropertyName]) ? true : false;
  }

  public function get($PropertyName, $DefaultValue = false)
  {
    try {
      return $this->Data[$PropertyName];
    } catch (\Exception | \Throwable $e) {
      if (func_num_args() == 2) {
        return $DefaultValue;
      } else {
        throw new \Exception($PropertyName . ' is not in $_REQUEST');
      }
    }
  }
}
