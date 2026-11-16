# Niirrty.IO – HANDOFF

Project / working directory: `/home/niirrty/Projekte/human/php/Niirrty.IO` (PHP library, namespace `Niirrty\IO`).
Goal: PHPUnit tests for EVERY class defined inside `src/`. One task = one class.

**Never do anything outside the working directory. Never run a task without the user explicitly says so!**

Every task is specified complete in its own journal file (`journal/`), so it can be executed in a fresh
context without any knowledge of the other tasks. Details NOT inside this file stay in the journal files.
`HANDOFF.md` and the `journal/` folder are NOT part of the git repository.

## Global rules (apply to every task)

1. Only work inside the working directory.
2. Run a task only on explicit user instruction – HANDOFF.md alone never starts a task.
3. Each task ends with exactly one `git commit` – and NEVER with a push (no remote access at all).
4. Commit only the files the task created/changed: explicit `git add <pfad>`; never `git add .` / `-A` / `git commit -a`.
5. `HANDOFF.md` and `journal/` must never be added to git (they stay untracked).
6. All test files are part of the repository and stay committed – tests + test environment get reused later.
7. Task order matters: Task 00 (test environment) first, `IOException` (01) before its subclasses.
8. Status: mark a task `[x]` in the table below only after it was executed and committed.

## PHP rules

1. Each PHP file must start with `<?php`, never us short open tag `<?`
2. Ignore the #region and #endregion comments! They are only used for code folding.

## Tasks

| # | Task / class (src file) | Journal file | Status |
|---|-------------------------|--------------|--------|
| 00 | PHPUnit test environment setup | `journal/00-phpunit-setup.md` | [x] |
| 01 | `IOException` (`src/IOException.php`) | `journal/01-ioexception.md` | [x] |
| 02 | `FileAccessException` (`src/FileAccessException.php`) | `journal/02-file-access-exception.md` | [x] |
| 03 | `FileAlreadyExistsException` (`src/FileAlreadyExistsException.php`) | `journal/03-file-already-exists-exception.md` | [x] |
| 04 | `FileFormatException` (`src/FileFormatException.php`) | `journal/04-file-format-exception.md` | [x] |
| 05 | `FileNotFoundException` (`src/FileNotFoundException.php`) | `journal/05-file-not-found-exception.md` | [x] |
| 06 | `FolderAccessException` (`src/FolderAccessException.php`) | `journal/06-folder-access-exception.md` | [x] |
| 07 | `FolderNotFoundException` (`src/FolderNotFoundException.php`) | `journal/07-folder-not-found-exception.md` | [x] |
| 08 | `Path` (`src/Path.php`) | `journal/08-path.md` | [x] |
| 09 | `MimeTypeTool` (`src/MimeTypeTool.php`) | `journal/09-mimetype-tool.md` | [ ] |
| 10 | `Folder` (`src/Folder.php`) | `journal/10-folder.md` | [ ] |
| 11 | `File` (`src/File.php`) | `journal/11-file.md` | [ ] |

`src/required-code.php` defines the global constant `IS_WIN` (no class) → no own task; it is loaded
automatically by `vendor/autoload.php` (see Task 00).

## Notes / pitfalls found in finished tasks (read together with the journal files!)

- **First use of a class can throw**: Niirrty.Core installs a global error handler that converts
  PHP notices/deprecation warnings into `Niirrty\Php*Exception`s. On PHP 8.5 the `src/` code uses
  soft-nullable parameters (`string $x = null`) e.g. in `IOException::__construct()` and in
  `FileAccessException::Read()`/`Write()`, and such parameters are deprecated → that notice is
  emitted at the class definition (deferred class compilation), so the FIRST use of that class in
  a process throws an exception, and every later use is clean. With `processIsolation="false"` it
  happens only once per run: the test class of such a class must assert that behaviour in its very
  FIRST test method, so the method order inside that test class is relevant - keep the
  deprecation test first when adding new tests to it.
- Exception classes are library classes and have no filesystem side effects, but they still must be
  tested for their full API (constructor behaviour, factory/static methods, getters, inheritance,
  `__toString`, error handling).
- **Task 06 (2026-02-06, commit `f7853c4`)**: `FolderAccessException` is clean at its class definition
  (its ctor declares explicit nullable parameters) → NO deprecation-exception on first use, in
  contrast to `FileAccessException` (task 02). Its `ACCESS_*` constants are NOT `final`.
  Gotchas asserted as CURRENT behaviour: appendix uses `!empty()` → appendix `'0'` silently lost;
  ctor `$code` is `string|int` but a string code always raises `Niirrty\PhpException`
  (`Code-Parameter has to be int, but given: string.`), factory methods raise a plain `TypeError`;
  unknown codes (e.g. 4711) make `getErrorMessage()` throw
  `Constant E_STRICT is deprecated since 8.4, the error level was removed`; the class' own
  `@expects` docs in `src/` show the wrong message text (`There was an error …` instead of
  `There was a error …`). PHP 8.5 reflection: `ReflectionClassConstant::isStatic()` and
  `ReflectionMethod::getReturnTypeString()` do not exist – use `(string) getReturnType()`.
- **Task 07 (2026-11-16, commit `79b5189`)**: `FolderNotFoundException` is the folder counterpart of
  `FileNotFoundException` (task 05) and clean at its class definition (explicit nullable
  `?string $message`, `?\Throwable $previous`) → NO deprecation-exception on first use. Its ctor
  parameter is named `folder` (not `path`/`file`), and – unlike `FolderAccessException` (task 06) –
  it declares NO factory method, NO own accessor and no constants. Gotchas asserted as CURRENT
  behaviour: own fixed text `'The Folder not exists.'`; appendix handled by `empty()` → `'0'`
  silently lost, `' '` appended; default `$code` is the constant `\E_USER_ERROR` (= 256, itself NOT
  deprecated) but a `string` code still raises the `TypeError` from `Exception::__construct()`;
  `getErrorMessage()` throws `Constant E_STRICT is deprecated since 8.4, the error level was removed`
  also for the codes `0` and `-1` (not only for `4711`); a wrong named parameter (`file:`) raises a
  plain `Error` (`Unknown named parameter $file`), not a `TypeError`; `ReflectionClass::getProperties()`
  also returns the inherited `_trace`/`message`/`code`/`file`/`line` – filter with
  `getDeclaringClass()` (full details see `journal/07-folder-not-found-exception.md`).
