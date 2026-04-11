# WordPress Block API v3 Migration Guide

## Overview

This document covers all updates required to migrate WordPress Gutenberg blocks from Block API v2 to v3, and fix related deprecation warnings introduced in WordPress 6.8+.

---

## 1. Update `apiVersion` from 2 to 3

**Why:** Block API v2 is deprecated since WordPress 6.9. All editors will use iframes in the future, and `apiVersion: 3` declares iframe editor compatibility.

**In `block.json` files:**

```json
// Before
"apiVersion": 2,

// After
"apiVersion": 3,
```

**In `block.js` files (registerBlockType):**

```javascript
// Before
apiVersion: 2,

// After
apiVersion: 3,
```

**In compiled `dist/*.js` files:**
Replace all `apiVersion:2` with `apiVersion:3` in minified bundles, or rebuild from source.

---

## 2. Add `__next40pxDefaultSize` prop to all form controls

**Why:** The 36px default size is deprecated since WordPress 6.8 and will be removed in 7.1. Controls must opt into the new 40px default.

**Affected components from `wp.components`:**

- `TextControl`
- `RangeControl`
- `SelectControl`
- `UnitControl` (`__experimentalUnitControl`)
- `ToggleGroupControl` (`__experimentalToggleGroupControl`)

**Example fix:**

```javascript
// Before
<RangeControl
    label={ __( 'Size' ) }
    value={ size }
    onChange={ ( value ) => setAttributes( { size: value } ) }
    min={ 1 }
    max={ 100 }
/>

// After
<RangeControl
    __next40pxDefaultSize
    label={ __( 'Size' ) }
    value={ size }
    onChange={ ( value ) => setAttributes( { size: value } ) }
    min={ 1 }
    max={ 100 }
/>
```

**For `ToggleGroupControl`, also add `__nextHasNoMarginBottom`:**

```javascript
<ToggleGroupControl
    __next40pxDefaultSize
    __nextHasNoMarginBottom
    label={ __( 'Unit' ) }
    value={ unit }
    onChange={ ( value ) => setAttributes( { unit: value } ) }
    isBlock
>
```

---

## 3. Replace deprecated `ButtonGroup` with `ToggleGroupControl`

**Why:** `wp.components.ButtonGroup` is deprecated since WordPress 6.8.

**Before:**

```javascript
const { ButtonGroup, Button } = wp.components;

<ButtonGroup
    className="size-type-options"
    aria-label={ __( 'Width Type' ) }
>
    { map( unitList, ( { name, key } ) => (
        <Button
            key={ key }
            isSmall
            isPrimary={ unit === key }
            aria-pressed={ unit === key }
            onClick={ () => setAttributes( { unit: key } ) }
        >
            { name }
        </Button>
    ) ) }
</ButtonGroup>
```

**After:**

```javascript
const {
    __experimentalToggleGroupControl: ToggleGroupControl,
    __experimentalToggleGroupControlOption: ToggleGroupControlOption,
} = wp.components;

<ToggleGroupControl
    __next40pxDefaultSize
    __nextHasNoMarginBottom
    label={ __( 'Width Type' ) }
    value={ unit }
    onChange={ ( value ) => setAttributes( { unit: value } ) }
    isBlock
>
    { map( unitList, ( { name, key } ) => (
        <ToggleGroupControlOption
            key={ key }
            value={ key }
            label={ name }
        />
    ) ) }
</ToggleGroupControl>
```

---

## 4. Replace deprecated `useSetting` with `useSettings`

**Why:** `wp.blockEditor.useSetting` (singular) is deprecated since WordPress 6.5.

**Before:**

```javascript
const { useSetting } = wp.blockEditor;

const units = useCustomUnits( {
    availableUnits: useSetting( 'spacing.units' ) || defaultUnits,
} );
```

**After:**

```javascript
const { useSettings } = wp.blockEditor;

const [ spacingUnits ] = useSettings( 'spacing.units' );
const units = useCustomUnits( {
    availableUnits: spacingUnits || defaultUnits,
} );
```

**Key difference:** `useSettings` returns an **array** (one value per setting path), so destructure with `const [ value ] = useSettings( 'path' )`.

---

## 5. Prevent `href="#"` navigation in editor demo content

**Why:** In API v3, blocks run inside an iframe. Clicking `<a href="#">` navigates the iframe instead of doing nothing.

**Before:**

```javascript
<a href="#">{ __( 'Item Name' ) }</a>
```

**After:**

```javascript
<a href="#" onClick={ ( e ) => e.preventDefault() }>{ __( 'Item Name' ) }</a>
```

---

## 6. Fix `getBlockParentsByBlockName` return type handling

**Why:** `getBlockParentsByBlockName()` returns an **array** of client IDs, not a single string. Passing the array directly to `getBlockAttributes()` returns `null`.

**Before:**

```javascript
const rootFormClientId = select( 'core/block-editor' )
    .getBlockParentsByBlockName( clientId, 'my-plugin/form' );
const rootFormAttributes = select( 'core/block-editor' )
    .getBlockAttributes( rootFormClientId );
const { align } = rootFormAttributes; // TypeError: Cannot read properties of null
```

**After:**

```javascript
const rootFormClientIds = select( 'core/block-editor' )
    .getBlockParentsByBlockName( clientId, 'my-plugin/form' );
const rootFormClientId = rootFormClientIds && rootFormClientIds.length > 0
    ? rootFormClientIds[ 0 ] : null;
const rootFormAttributes = rootFormClientId
    ? select( 'core/block-editor' ).getBlockAttributes( rootFormClientId ) : null;
const align = rootFormAttributes ? rootFormAttributes.align : undefined;
```

Also add null checks for DOM queries in `styles.js` files:

```javascript
const currentElement = document.getElementById( 'block-' + clientId );
if ( currentElement ) {
    const searchBarElement = currentElement.parentElement.querySelector( '.container' );
    if ( searchBarElement ) {
        const inputElement = searchBarElement.querySelector( '.input' );
        if ( inputElement ) {
            // safe to read offsetWidth, offsetLeft, etc.
        }
    }
}
```

---

## Quick Scan Commands

Find all files needing updates:

```bash
# Find blocks with apiVersion 1 or 2
grep -rn '"apiVersion": [12]' src/ --include='*.json' --exclude-dir=node_modules
grep -rn 'apiVersion: [12]' src/ --include='*.js' --exclude-dir=node_modules

# Find controls missing __next40pxDefaultSize
grep -rn '<TextControl\|<RangeControl\|<SelectControl\|<UnitControl\|<ToggleGroupControl[^O]' \
    src/ --include='*.js' --exclude-dir=node_modules

# Find deprecated ButtonGroup
grep -rn 'ButtonGroup' src/ --include='*.js' --exclude-dir=node_modules

# Find deprecated useSetting (singular)
grep -rn 'useSetting[^s]' src/ --include='*.js' --exclude-dir=node_modules

# Find href="#" in editor files
grep -rn 'href="#"' src/ --include='*.js' --exclude-dir=node_modules

# Find getBlockParentsByBlockName passed directly to getBlockAttributes
grep -rn 'getBlockParentsByBlockName' src/ --include='*.js' --exclude-dir=node_modules
```

---

## Verification

After applying all fixes, verify with:

```bash
# Confirm no apiVersion 2 remains
grep -rn '"apiVersion": 2\|apiVersion: 2\|apiVersion:2' \
    src/ dist/ --include='*.json' --include='*.js' --exclude-dir=node_modules

# Confirm all controls have __next40pxDefaultSize
# (this awk checks if the line after each control tag has the prop)
for f in $(grep -rl '<TextControl\|<RangeControl\|<SelectControl\|<UnitControl\|<ToggleGroupControl[^O]' \
    src/ --include='*.js' --exclude-dir=node_modules); do
  awk '/<(TextControl|RangeControl|SelectControl|UnitControl|ToggleGroupControl)[^O]/ || \
       /<(TextControl|RangeControl|SelectControl|UnitControl|ToggleGroupControl)$/ \
       { found=1; line=NR; next } \
       found && NR==line+1 { \
         if ($0 !~ /__next40pxDefaultSize/) \
           print FILENAME ":" line ": MISSING __next40pxDefaultSize"; \
         found=0 \
       }' "$f"
done
```
