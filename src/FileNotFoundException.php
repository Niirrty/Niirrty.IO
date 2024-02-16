<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2021, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.5.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * This exception should be used if a required file don't exist.
 *
 * The class extends from {@see IOException}.
 *
 * @since v0.1
 */
class FileNotFoundException extends IOException
{


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Init a new instance
     *
     * @param string          $file     The missed file.
     * @param string|null     $message  The optional error message
     * @param string|int      $code     The optional error code (Default to \E_USER_ERROR)
     * @param \Throwable|null $previous optional previous exception
     */
    public function __construct(
        string $file, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    {

        parent::__construct(
            $file,
            'File not exists.' . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


}

