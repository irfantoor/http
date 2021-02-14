<?php

/**
 * IrfanTOOR\Http\Cookie
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR\Http;

use IrfanTOOR\Collection;

/**
 * Cookie to manage the Request, ServerRequest or Response cookies
 */
class Cookie extends Collection
{
    protected $data = [];

    /**
     * Constructs a cookie from provided key, value pair(s) and options
     */
    public function __construct($init = [])
    {
        $value = $init['value'] ?? null;

        $this->data = [
            'name'     => $init['name'] ?? 'undefined',
            'value'    => $value,
            'expires'  => $init['expires'] ?? ($value ? time() + 24 * 60 * 60 : 1),
            'path'     => $init['path'] ?? "/",
            'domain'   => $init['domain'] ?? $_SERVER['SEVRER_NAME'] ?? 'localhost',
            'secure'   => $init['secure'] ?? false,
            'httponly' => $init['httponly'] ?? false,
        ];
    }

  function send($Name, $Value = '', $Expires = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
  {
    if (!empty($Domain))
    {
      // Fix the domain to accept domains with and without 'www.'.
      if (strtolower(substr($Domain, 0, 4)) == 'www.')  $Domain = substr($Domain, 4);
      $Domain = '.' . $Domain;

      // Remove port information.
      $Port = strpos($Domain, ':');
      if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
    }

    header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
                          . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
                          . (empty($Path) ? '' : '; path=' . $Path)
                          . (empty($Domain) ? '' : '; domain=' . $Domain)
                          . (!$Secure ? '' : '; secure')
                          . (!$HTTPOnly ? '' : '; HttpOnly'), false);
  }
}
