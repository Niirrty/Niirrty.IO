<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2026, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.6.4
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * This exception should be used if an accessed file contains a wrong/bad format.
 *
 * The class extends from {@see IOException}.
 *
 * @since v0.1
 */
class FileFormatException extends IOException
{


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Init a new instance
     *
     * @param string          $file     The bad formatted file.
     * @param string|null     $message  The optional error message
     * @param string|int      $code     The optional error code (Default to \E_USER_ERROR)
     * @param \Throwable|null $previous optional previous exception
     */
    public function __construct(
        string $file, ?string $message = null, string|int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    {

        parent::__construct(
            $file,
            'File format is wrong or illegal.' . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


}

