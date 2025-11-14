## AI Contribution Guidelines: Enhanced

Follow these mandatory guidelines when contributing code, documentation, and translations to this repository. All contributions will be reviewed for compliance.

---

## General Project Overview

* **Project Scope:** This is a **standalone third-party Symfony bundle**.
* **Compatibility:** Code **must** be compatible with **Symfony 6.4, and 7.x** versions.
* **Target PHP:** Use **modern PHP 8.2** syntax and features.
* **Deprecations:** **Avoid** using deprecated Symfony or PHP features. Consult the [Symfony Backward Compatibility Promise](https://symfony.com/doc/current/contributing/code/bc.html#working-on-symfony-code) for accepted changes during review.
* **Readability:** Break text lines in documentation at **~72–78 characters** for optimal readability.

---

## PHP Code Standards

Adhere to the **Symfony Coding Standards and Best Practices** alongside the following specific rules:

### Fundamentals

* **PSR Compliance:** Follow **PSR-1, PSR-2, PSR-4, and PSR-12** standards.
* **Strictness:** Always use **strict comparisons (`===`)** and **not loose (`==`)**.
* **Yoda Conditions:** Use **Yoda conditions** for comparisons (e.g., `if (null === $value)`).
* **Braces:** Use braces **`{}`** in all control structures, even for one-liners.
* **One Class Per File:** Use **one class per file** (except for tests).
* **Trailing Commas:** Use **trailing commas** in multi-line arrays and parameter lists.

### Structure and Organization

* **Class Order:**
  1.  Constructor (`__construct()`)
  2.  Test Setup/Teardown (`setUp()` / `tearDown()`)
  3.  Class properties
  4.  Methods (ordered: **`public` → `protected` → `private`**)
* **Return Statements:**
  * Add a **blank line before `return`**, unless it is the only line in the block.
  * Use **`return null;`** for nullable returns and **`return;`** for void functions.
* **Control Flow:** **Avoid `else`, `elseif`, or `break`** after a block that returns or throws.

### Naming Conventions

* **Case Conventions:**
  * **`camelCase`**: Variables and method names.
  * **`snake_case`**: Configuration keys, routing names, and Twig template/asset names.
  * **`SCREAMING_SNAKE_CASE`**: Class constants.
  * **`UpperCamelCase` (PascalCase)**: PHP class, interface, trait, and enum names.
* **Suffixing/Prefixing:**
  * **Interfaces**: Suffix with **`Interface`** (e.g., `MyInterface`).
  * **Traits**: Suffix with **`Trait`** (e.g., `MyTrait`).
  * **Exceptions**: Suffix with **`Exception`** (e.g., `MyException`).
  * **Abstract Classes**: Prefix with **`Abstract`**, except for test cases (e.g., `AbstractFactory`).

### Error Handling

* **Exception Messages:**
  * Start with a **capital letter** and end with a **dot**.
  * **Do not** use backticks (`` ` ``).
  * Concatenate messages using **`sprintf()`**.
  * Use **`get_debug_type()`** for inserting class names into messages.
  * Error messages must be **concise but very precise**.
* **Handling:** Handle exceptions **explicitly** and **avoid silent catch blocks** (i.e., blocks that catch an exception and do nothing).

### PHP Comments & PHPDoc

* **Code Comments:** Add code comments **only for complex or unintuitive code**.
* **PHPDoc Blocks:**
  * **Do not** use one-line docblocks.
  * **Avoid `@return`** if the method returns `void`.
  * Group annotations by type.
  * Put **`null` last** in union types (e.g., `string|null`).

---

## Bundle Configuration

* **Dependency Injection:** **Do not use service autowiring**; configure all services explicitly.
* **Service Configuration:** Service configuration **must** use the **PHP format** (`config/services.php`).
* **Instantiation:** Use **parentheses when instantiating classes**, even without arguments (`new MyClass();`).
* **`use` Statements:** Add `use` statements for **all non-global classes**.

---

## Documentation, Examples & Assets

### Documentation Format

* **Location:** Documentation is stored in the **`docs/` directory**.
* **Syntax:** Use the **reStructuredText** syntax.
* **Headings:** Use the specified heading symbols for levels 1–5: **`=`, `-`, `~`, `.`, `"`**.
* **Code Blocks:** Prefer **`::`** over `.. code-block:: php` unless it causes formatting issues.
* **Hyperlinks:** **Separate link text and its URL** (avoid inline hyperlinks).

### Code Examples

* **Realism:** Use **realistic and meaningful examples**; avoid generic placeholders like `foo`, `bar`, etc.
* **Placeholders:** Use **`Acme`** for vendor names and **`example.com` / `example.org` / `example.net`** for URLs.
* **Directory Root:** Use **`your-project/`** as the root directory in examples.
* **Line Length:** Break code lines **$>85$ characters**; use **`...`** to indicate folded code.
* **Dependencies:** **Include `use` statements** when showing referenced classes.
* **Command Line:** Prefix bash lines with **`$`**. Show the filename as a comment when useful.
* **Configuration Order:** Show all configuration formats in this order: **YAML, XML, PHP** (or Attributes when applicable).

### Strings and Assets

* **Quotes:** Never use **typographic quotes** (curly quotes); use **straight quotes** only: **`'` and `"`**.
* **String Wraps:** Wrap strings (in PHP, CSS, and JavaScript) with **single straight quotes (`'`)**.
* **Paths:**
  * Add **trailing slashes** when referencing directories (e.g., `src/`).
  * Use a **leading dot** for file extensions (e.g., `.html.twig`).
* **Twig/Assets:** Use **`snake_case`** for Twig templates and assets (e.g., `template.html.twig`, `style.scss`).

### Language and Tone

* **Language:** Write in **American English**.
* **Contractions:** Contractions are **allowed** (e.g., "it's," "you're").
* **Person:** Use the **second person (`you`)** and **avoid the first person (`we`)**.
* **Inclusivity:** Use **gender-neutral language** (`they/them`).
* **Tone:** **Avoid belittling or exclusionary words** (e.g., "just," "obviously," "easy").

---
