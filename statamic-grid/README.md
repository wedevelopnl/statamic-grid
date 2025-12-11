# Statamic Grid

A flexible grid system addon for Statamic with sections, rows, and elements.

## Installation

Add the repository to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "addons/wedevelopnl/statamic-grid"
        }
    ]
}
```

Then require the package:

```bash
composer require wedevelopnl/statamic-grid
```

Publish the fieldsets:

```bash
php artisan vendor:publish --tag=statamic-grid
```

## Usage

### Option 1: Automatic Rendering

Use the `grid:render` tag to automatically render your grid:

```antlers
{{ grid:render }}
```

With a custom field name:

```antlers
{{ grid:render :data="my_custom_grid" }}
```

### Option 2: Manual Rendering

For full control, loop through the grid manually using the addon's partials:

```antlers
{{ grid }}
    {{ partial src="wedevelopnl/statamic-grid::grid/section" }}
{{ /grid }}
```

Available partials:
- `statamic-grid::grid/section` - Renders a section with background color
- `statamic-grid::grid/row` - Renders a row with elements
- `statamic-grid::elements/text` - Renders a text element (Bard content)
- `statamic-grid::elements/image` - Renders an image element

### Adding the Grid to Your Blueprint

Import the grid fieldset in your page blueprint:

```yaml
tabs:
  main:
    fields:
      - import: grid
```

## Structure

The grid system follows this hierarchy:

- **Grid** - Container for sections
  - **Section** - Full-width section with background color (light/dark)
    - **Row** - Horizontal row spanning the section
      - **Elements** - Content blocks (text, image, etc.)

## Extending

To add custom element types:

1. Create a fieldset in `resources/fieldsets/element_yourtype.yaml`
2. Import it in `resources/fieldsets/element.yaml`
3. Create a view at `resources/views/vendor/statamic-grid/elements/yourtype.antlers.html`

## License

MIT
