<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * This exception should be used if a required file do'nt exists.
 *
 * The class extends from {@see \Niirrty\IO\IOException}.
 *
 * @since v0.1
 */
class FileNotFoundException extends IOException
{


    # <editor-fold desc=" - - >   P U B L I C   C O N S T R U C T O R   - - - - - - - - - - - - - - - - - -">


    /**
     * Init's a new instance
     *
     * @param string     $file     The missed file.
     * @param string     $message  The optional error message
     * @param int        $code     The optional error code (Default to \E_USER_ERROR)
     * @param \Throwable $previous A optional previous exception
     */
    public function __construct(
        string $file, string $message = null, int $code = \E_USER_ERROR, \Throwable $previous = null )
    {

        parent::__construct(
            $file,
            'File not exists.' . static::appendMessage( $message ),
            $code,
            $previous
        );

    }


    # </editor-fold>

}

