# Task 11 – `File`

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote. Exactly ONE `git commit` at the end of the task.
- Commit only the files this task created/changed: `git add <explicit Pfad>` – never `git add .` / `-A` / `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository and stay committed.
- Execute this task only if the user explicitly says so.

## Precondition

Task 00 done/committed (PHPUnit environment: `phpunit.xml.dist`, `tests/boot.php`, runner `vendor/bin/phpunit`).
`File` internally uses `Niirrty\IO\Path` and its own `FileAccessException`/`FileNotFoundException` classes
(source code is enough – other tests not required).

## Target class

`src/File.php` (1561 lines) – class with instance methods (created only via the static factory methods;
the ctor `__construct( private readonly string $file, private readonly string $access, private $fp,
private readonly int $mode, private readonly bool $locked = false )` is PRIVATE) and many static helpers.
Throws `FileAccessException`, `FileNotFoundException`, `FileAlreadyExistsException`, `IOException`.

Constants: `ACCESS_READ = 'read'`, `ACCESS_WRITE = 'write'`, `ACCESS_READWRITE = 'read and write'`.

Instance API: `__destruct()` (closes!), `isOpen()`, `hasReadAccess()`, `hasWriteAccess()`,
`readLine( bool $removeNewlines = true, bool $fast = false )`, `read( int $count = 1, bool $fast = false )`,
`readChar( bool $fast = false )`, `readCsv( string $delimiter = ',', int $maxLineLength = 1024, bool $fast = false )`,
`readToEnd( bool $getLines = false, bool $removeNewlines = true, bool $fast = false )`,
`writeLine( string $str, string $newline = "\n", bool $fast = false )`, `writeLineFast( string $str, string $newline = "\n" )`,
`write( string|array $strOrArray, string $newline = "\n", bool $fast = false )`, `writeChars( string $chars, bool $fast = true )`,
`writeCsv( array $dataRow, string $delimiter = ',', bool $fast = false )`, `getPointerPosition()`,
`setPointerPosition( int $offset = 0 )`, `setPointerPositionToEndOfFile()`, `close()`, `getFileName()` (final).

Static API: `CreateNew( string $file, bool $overwrite = true, bool $lock = false, bool $lockExclusive = false, int $mode = 0755 )`,
`OpenForAppend( string $file, bool $lock = false, bool $lockExclusive = false, int $mode = 0755 )`,
`OpenRead( string $file, bool $lock = false, bool $lockExclusive = false )`,
`Create( string $file, int $mode = 0750, bool $overwrite = true, array|string $contents = '' )`,
`Delete( string $file )`, `Move( string $srcFile, string $targetFile, bool $replace = true )`,
`Copy( string $sourceFile, string $targetFile, bool $overwrite = true )`,
`ReadFirstBytes( string $file, int $count )`, `Zip( string $sourceFile, string $zipFile, ?string $workingDir = null )`,
`ZipList( array $sourceFiles, string $zipFile, ?string $zipFolderName = null )`,
`UnZip( string $zipFile, string $targetFolder, bool $clearTarget = true )`,
`UnZipSingleFile( string $zipFile, string $zippedFileName, string $targetFile )`,
`GetZipArchiveError( int code )`, `GetExtension( string $file, bool $doubleExtension = false )`,
`GetExtensionName( string $file, bool $doubleExtension = false )`,
`GetNameWithoutExtension( string $file, bool $doubleExtension = false )`,
`ChangeExtension( string $file, string $newExtension, bool $doubleExtension = false, bool $handle = false )`.

## Test file / naming

- `tests/Nirrty/Tests/IO/FileTest.php`
- `namespace Niirrty\Tests\IO; class FileTest extends TestCase`
- Source-like header docblock, methods `test<Method>…`
- The class is BIG: split by concern into several test methods per method
  (e.g. `testReadLine`, `testReadLineEof`, `testReadCsvCustomDelimiter`, …). One file, many methods.
- Reusable helper inside the test class: `createFixtureDir( string $name )` + `cleanUpFixtureDirs()`;
  call cleanup in `tearDown()`.

## Filesystem rules (important!)

- NEVER touch anything outside the working directory, NEVER use `/tmp` or the user's home.
- All data goes into `tests/tmp/FileTest/<case>/…` (gitignored via `tests/tmp/` added in Task 00);
  delete that root in `tearDown()` so `git status` stays clean after the run.
- Always `close()` instance handles explicitly (the `__destruct()` also closes – test that behaviour
  deliberately, e.g. by dropping the instance and checking the file content afterwards).
- `ZipArchive` IS available in this environment (`class_exists('ZipArchive')` → true) → the
  "no ZIP support" exception branch cannot be triggered; state that in the test instead of skipping.
- File modes/permissions are Linux → assert only POSIX behaviour.

## Steps

1. Read the complete `src/File.php` (all 1561 lines) before writing tests; derive expectations from the
   code, not from the docblocks.
2. Cover, per concern:
   - factory methods: `CreateNew` (new + existing file with/without `$overwrite`), `OpenRead`
     (non-existing file → exception!), `OpenForAppend` (new/existing)
   - instance state: `isOpen()`, `hasReadAccess()`, `hasWriteAccess()`, `getFileName()`
   - reading: `readLine` (with/without newline removal, EOF → `false`), `read` (count, EOF), `readChar`,
     `readToEnd` (string vs. lines array, newline removal), `readCsv` (delimiter variants, `maxLineLength`)
   - writing: `writeLine` (incl. custom newline), `writeLineFast`, `write` (string + array), `writeChars`,
     `writeCsv` (incl. delimiter escaping)
   - pointer: `getPointerPosition`, `setPointerPosition`, `setPointerPositionToEndOfFile`
   - lifecycle: `close()` twice, access after close (document actual behaviour), `__destruct`
   - statics: `Create` (string + array contents, overwrite), `Delete` (missing file → exception),
     `Move` (replace on/off), `Copy` (overwrite on/off), `ReadFirstBytes` (count > filesize),
     extension helpers `GetExtension`/`GetExtensionName`/`GetNameWithoutExtension`/`ChangeExtension`
     (no extension, double extension, dot-only names, unicode names)
   - ZIP: `Zip`, `ZipList`, `UnZip`, `UnZipSingleFile`, `GetZipArchiveError` (only with ZipArchive, roundtrip
     in the fixture dir; verify content afterwards)
   - locking: `$lock`/`$lockExclusive` parameters (light: just that they work and release the lock)
3. Run `vendor/bin/phpunit tests/Nirrty/Tests/IO/FileTest.php` → green AND afterwards
   `git status` must show no leftovers.
4. Never change `src/`! Any bug found: assert the actual behaviour + document in a "Findings" section here.

## Commit (exactly these files)

```
git add tests/Nirrty/Tests/IO/FileTest.php
git commit -m "test(File): PHPUnit tests for Niirrty\\IO\\File"
```
