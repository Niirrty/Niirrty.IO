<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2021, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * This exception should be used if a accessed file contains a wrong/bad format.
 *
 * The class extends from {@see \Niirrty\IO\IOException}.
 *
 * @since v0.1
 */
class FileFormatException extends IOException
{


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =


    /**
     * Init's a new instance
     *
     * @param string          $file     The bad formatted file.
     * @param string|null     $message  The optional error message
     * @param int             $code     The optional error code (Default to \E_USER_ERROR)
     * @param \Throwable|null $previous A optional previous exception
     */
    public function __construct(
        string $file, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
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

