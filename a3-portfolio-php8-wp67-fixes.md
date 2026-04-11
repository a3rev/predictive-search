# a3 Portfolio Plugin — PHP 8.x & WordPress 6.7+ Fixes

This document details all fixes applied to the **a3 Portfolio** plugin and its addons to resolve PHP 8.x deprecation warnings and WordPress 6.7+ compatibility issues. Use this as a reference to apply the same patterns to other a3rev plugins that use the same framework architecture.

---

## Table of Contents

1. [Fix 1: WordPress 6.7+ Translation Loading Too Early](#fix-1-wordpress-67-translation-loading-too-early)
2. [Fix 2: Global Variable Access Before Initialization](#fix-2-global-variable-access-before-initialization)
3. [Fix 3: Array Offset on Null Global Settings](#fix-3-array-offset-on-null-global-settings)
4. [Fix 4: Deferred Global Variable in Hook Registration](#fix-4-deferred-global-variable-in-hook-registration)
5. [Fix 5: lessc.inc.php PHP 8.2+ Deprecations](#fix-5-lescinc-php-82-deprecations)

---

## Fix 1: WordPress 6.7+ Translation Loading Too Early

### Error

```
Notice: Function _load_textdomain_just_in_time was called incorrectly.
Translation loading for the a3-portfolio domain was triggered too early.
Translations should be loaded at the init action or later.
(This message was added in version 6.7.0.)
```

### Root Cause

The plugin's admin framework classes (settings pages, tabs) use `__('...', 'text-domain')` in their constructors. When `includes_framework()` was called directly during plugin load (in the main class `__construct()`), these translation calls happened before the `init` action — which is when WordPress 6.7+ expects text domains to be loaded.

### Fix Applied

**File: `admin/plugin-init.php`** (main plugin class constructor)

**Before:**
```php
public function __construct() {
    $this->includes_framework(); // Called directly during plugin load
    $this->includes();

    add_action( 'plugins_loaded', function() {
        if ( ! defined( 'A3_PORTFOLIO_TRAVIS' ) ) {
            a3_portfolio_set_global_page();
        }
    });

    add_action( 'init', array( $this, 'plugin_init' ), 8 );
    add_action( 'widgets_init', array( $this, 'register_widget' ) );
}
```

**After:**
```php
public function __construct() {
    // Include non-framework files only (no translation calls)
    $this->includes();

    // plugin_init loads textdomain, registers post types, image sizes
    add_action( 'init', array( $this, 'plugin_init' ), 1 );

    // Defer framework init to AFTER textdomain is loaded
    add_action( 'init', array( $this, 'includes_framework' ), 2 );

    add_action( 'init', function() {
        if ( ! defined( 'A3_PORTFOLIO_TRAVIS' ) ) {
            a3_portfolio_set_global_page();
        }
    }, 9 );

    add_action( 'widgets_init', array( $this, 'register_widget' ) );
}
```

**Key change:** `includes_framework()` is moved from direct call to `add_action('init', ..., 2)`, which runs AFTER `plugin_init()` (at init priority 1) where the textdomain is loaded.

### Also Update `includes_framework()` Method

Since `plugins_loaded` has already fired by the time `includes_framework()` runs at `init`, any logic that relied on `plugins_loaded` hooks inside the framework won't fire. Move that logic directly into `includes_framework()`:

```php
public function includes_framework() {
    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_interface'] = new FrameWork\Admin_Interface();

    // ... instantiate settings pages ...

    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init'] = new FrameWork\Admin_Init();
    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init']->init();

    // Load all settings directly since plugins_loaded has already fired
    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init']->get_all_settings();

    // Handle "just installed" setup
    if ( 'yes' === get_option( 'a3_portfolio_just_installed', 'no' ) ) {
        $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init']->set_default_settings();
        delete_metadata( 'user', 0,
            $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init']->plugin_name . '-'
            . 'plugin_framework_global_box' . '-' . 'opened', '', true
        );
        delete_option( 'a3_portfolio_just_installed' );
    }
}
```

### Also Clean Up `plugin_init()` Method

Remove the "just installed" setup code from `plugin_init()` since it's now in `includes_framework()`:

```php
public function plugin_init() {
    global $a3_portfolio_post_types;
    $a3_portfolio_post_types->register_image_sizes();
    $a3_portfolio_post_types->register_post_type();

    // Load textdomain BEFORE framework classes use __()
    a3_portfolio_plugin_textdomain();

    $this->upgrade_plugin();
}
```

---

## Fix 2: Global Variable Access Before Initialization

### Error

```
Warning: Undefined global variable $a3_portfolio_admin_init
Warning: Attempt to read property "plugin_name" on null
```

### Root Cause

Classes instantiated in `includes()` (which runs during plugin load) try to access `$GLOBALS[PREFIX.'admin_init']->plugin_name` in their constructors. But after Fix 1, `admin_init` is not created until `init` priority 2.

### Fix Applied

Replace direct access to the global's `plugin_name` property with the plugin's constant.

**File: `includes/backend/class-a3-portfolio-backend-scripts.php`**

Before:
```php
add_filter( $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_init']->plugin_name . '_plugin_extension_boxes',
    array( $this, 'plugin_extension_box' ) );
```

After:
```php
add_filter( A3_PORTFOLIO_KEY . '_plugin_extension_boxes',
    array( $this, 'plugin_extension_box' ) );
```

**File: `includes/addons/class-a3-portfolio-addons-page.php`** — Same fix.

### General Pattern

Any place in `includes()` classes that references `$GLOBALS[PREFIX.'admin_init']->plugin_name` or `$GLOBALS[PREFIX.'admin_interface']` should use the constant instead, since these globals don't exist yet at plugin load time. The `plugin_name` value is derived from the `_KEY` constant anyway.

---

## Fix 3: Array Offset on Null Global Settings

### Error

```
Warning: Trying to access array offset on value of type null
Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated
```

### Root Cause

`register_image_sizes()` runs at `init` priority 1 and accesses `$a3_portfolio_global_settings`, but this global is only populated by `get_all_settings()` at `init` priority 2. The global is `null` when accessed.

### Fix Applied

**File: `includes/post-types/a3-portfolio-post-types.php`** — `register_image_sizes()` method

1. Add a fallback to load settings directly from the database if the global isn't set yet.
2. Add `isset()` checks before accessing array offsets.

```php
public function register_image_sizes() {
    global $a3_portfolio_global_settings;

    // Fallback: load directly if global not yet populated
    if ( ! is_array( $a3_portfolio_global_settings ) ) {
        $a3_portfolio_global_settings = get_option( 'a3_portfolio_global_settings', array() );
    }

    $item_card_image_width = 400; // default
    $item_card_image_height = 400;
    $item_card_image_crop = false;

    if ( isset( $a3_portfolio_global_settings['item_card_image_width'] )
         && (int) trim( $a3_portfolio_global_settings['item_card_image_width'] ) > 0 ) {
        $item_card_image_width = (int) $a3_portfolio_global_settings['item_card_image_width'];
    }
    // ... repeat isset() check for every array key access ...
}
```

### General Pattern

Any function that accesses global settings arrays before they are guaranteed to be initialized should:
1. Check `if ( ! is_array( $global_var ) )` and load with `get_option()` as fallback.
2. Use `isset()` before accessing array offsets.

---

## Fix 4: Deferred Global Variable in Hook Registration

### Error

```
Warning: Undefined global variable $a3_portfolio_admin_interface
```

### Root Cause

When `add_action('admin_footer', array($GLOBALS[PREFIX.'admin_interface'], 'method'))` is called at `init` priority 0, PHP resolves `$GLOBALS[PREFIX.'admin_interface']` immediately — but it's `null` at that point (created at `init` priority 2).

### Fix Applied

**File: `includes/taxonomies/a3-portfolio-cat.php`** — `include_script()` method

Wrap callbacks in closures so the global is resolved at execution time, not registration time:

Before:
```php
add_action( 'admin_footer',
    array( $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_interface'], 'admin_script_load' ) );
add_action( 'admin_footer',
    array( $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_interface'], 'admin_css_load' ) );
```

After:
```php
add_action( 'admin_footer', function() {
    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_interface']->admin_script_load();
} );
add_action( 'admin_footer', function() {
    $GLOBALS[A3_PORTFOLIO_PREFIX.'admin_interface']->admin_css_load();
} );
```

### General Pattern

Anywhere that registers a hook callback using `array($GLOBALS[...], 'method')` and the global isn't set at registration time, wrap it in a closure. The closure defers resolution of the global to when the hook actually fires (which is much later, when the global is guaranteed to exist).

---

## Fix 5: lessc.inc.php PHP 8.2+ Deprecations

### Errors

```
Deprecated: Using ${var} in strings is deprecated, use {$var} instead
Deprecated: Creation of dynamic property a3_lessc_parser::$inExp is deprecated
Deprecated: Creation of dynamic property a3_lessc::$env is deprecated
Deprecated: Creation of dynamic property a3_lessc::$scope is deprecated
Deprecated: Creation of dynamic property a3_lessc::$formatter is deprecated
Deprecated: Creation of dynamic property a3_lessc_formatter_lessjs::$indentLevel is deprecated
Deprecated: preg_match(): Passing null to parameter #4 ($flags) of type int is deprecated
Fatal error: Cannot access protected property a3_lessc_parser::$writeComments
```

### Files Affected

All copies of `lessc.inc.php` across plugins:
- `a3-portfolio/admin/less/lib/lessc.inc.php`
- `a3-portfolio-dynamic-gallery/includes/less-compiler/libraries/lessc.inc.php`
- `a3-portfolio-dynamic-stylesheets-addon/includes/less-compiler/libraries/lessc.inc.php`
- `a3-portfolio-item-switcher-addon/includes/less-compiler/libraries/lessc.inc.php`

### Fix 5a: String Interpolation `${var}` → `{$var}`

Search for `${` in strings and replace with `{$`:

```php
// Before:
$this->throwError("${name}expecting...");
$fname = "op_${ltype}_${rtype}";

// After:
$this->throwError("{$name}expecting...");
$fname = "op_{$ltype}_{$rtype}";
```

### Fix 5b: Dynamic Property Declarations

Add explicit property declarations to each class. Properties that are assigned in the class but never declared trigger PHP 8.2 deprecation.

**`a3_lessc` class — add these properties:**
```php
class a3_lessc {
    // ... existing properties ...

    protected $parser = null;
    protected $env = null;
    protected $scope = null;
    protected $formatter = null;

    protected $allParsedFiles = array();
    // ... rest of class ...
}
```

**`a3_lessc_parser` class — add these properties:**
```php
class a3_lessc_parser {
    static protected $nextBlockId = 0;

    protected $eatWhiteDefault = true;
    protected $lessc = null;
    protected $sourceName = null;
    public $writeComments = false;    // public — accessed by a3_lessc
    public $count = 0;               // public — accessed by a3_lessc
    protected $line = 1;
    protected $env = null;
    public $buffer = '';             // public — accessed by a3_lessc
    protected $inExp = false;        // ← ADD THIS
    protected $seenComments = array();
    // ... rest of class ...
}
```

**Important:** `writeComments`, `count`, and `buffer` must be `public` (not `protected`) because the `a3_lessc` class accesses them directly on `a3_lessc_parser` instances. Using `protected` will cause:
```
Fatal error: Cannot access protected property a3_lessc_parser::$writeComments
```

**`a3_lessc_formatter_classic` class — add `$indentLevel`:**
```php
class a3_lessc_formatter_classic {
    public $indentChar = "  ";
    // ... other properties ...
    public $compressColors = false;

    public $indentLevel = 0;  // ← ADD THIS

    public function __construct() {
        $this->indentLevel = 0;
    }
    // ... rest of class ...
}
```

### Fix 5c: `preg_match()` Null Flags Parameter

In the `match()` and `peek()` methods, change `null` to `0`:

```php
// Before:
preg_match($r, $this->buffer, $out, null, $this->count);

// After:
preg_match($r, $this->buffer, $out, 0, $this->count);
```

There are typically 2 occurrences — one in the `match()` method and one in the `peek()` method.

---

## Summary of Init Hook Priority Order

After all fixes, the initialization order on the `init` hook is:

| Priority | What Runs | Why |
|----------|-----------|-----|
| 1 | `plugin_init()` — registers post types, image sizes, loads textdomain | Textdomain must load before framework |
| 2 | `includes_framework()` — instantiates admin framework, settings pages, calls `get_all_settings()` | Framework classes use `__()` in constructors |
| 9 | `a3_portfolio_set_global_page()` | Sets up global pages after framework is ready |

---

## Quick Checklist for Other Plugins

When applying these fixes to another a3rev plugin:

- [ ] **Move `includes_framework()` from constructor to `init` hook** (after textdomain loading priority)
- [ ] **Move `a3_..._set_global_page()` from `plugins_loaded` to `init` hook**
- [ ] **Add `get_all_settings()` call** directly in `includes_framework()` since `plugins_loaded` has already fired
- [ ] **Move "just installed" logic** from `plugin_init()` into `includes_framework()`
- [ ] **Replace `$GLOBALS[PREFIX.'admin_init']->plugin_name`** with the plugin's `_KEY` constant in any class instantiated during plugin load
- [ ] **Wrap `$GLOBALS[PREFIX.'admin_interface']` hook callbacks** in closures when registered before the global is created
- [ ] **Add fallback `get_option()` loading** for any global settings arrays accessed before `get_all_settings()` runs
- [ ] **Add `isset()` checks** before accessing settings array offsets
- [ ] **Fix `lessc.inc.php`**: `${var}` → `{$var}`, add missing property declarations, `preg_match` null → 0, ensure cross-class-accessed properties are `public`
