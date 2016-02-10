Breakout: A Context-Aware Escaping Tool
====================

*Licence:* BSD-3 Clause

Breakout is designed to provide context-aware escaping. For example, escaping things for HTML has different needs than Javascript or URL values.
The library will also handle the escaping of UTF-8 characters into their `\uXXXX` versions (it does this by default but it can be disabled by passing a `false` value in the `config` constructor configuration).

By default it will try to escape for a general HTML context, but you can specify any of the following:

- **html**: HTML output handling for use in the contents of a page
- **htmlAttr**: HTML encoding for use in HTML tag attributes
- **js**: Escape the string for inclusion in Javascript strings (converts to `\xHH` entities)
- **url**: Performs URL encoding on the provided data (can be a string or array)
- **css**: Escapes the data, stripping out key strings and translating certain values into `&#XX` formats

### Calling it manually

You can use the class directly (not through the helper or document rendering) by creating an object and using the `escape` method with your chosen context:

```php
<?php
require_once 'vendor/autoload.php';

use SalesforceEng\Breakout\Breakout as Breakout;

$b = new Breakout();
$str = 'this is a ☠ test with "something" here <script>alert("test");</script>';

echo 'html: '.$b->escape($str, 'html');
// result: this is a \u2620 test with &quot;something&quot; here &lt;script&gt;alert(&quot;test&quot;);&lt;\/script&gt;
?>
```

### Using with Symfony (v1)

To use the library in our Symfony installation, there needs to be two changes.

1. Move the file `BreakoutHelper.php` to `apps/frontend/lib/helper/BreakoutHelper.php`

2. And a change to the `apps/frontend/configuration/settings.yml` configuration file, adding it to the standard helpers list:

```
standard_helpers:       [Partial, Cache, Form, Javascript, UI, I18N, Breakout]
```

Oh, and refresh your cache...always refresh your cache...

## Filtering Types

These examples assume you're using the helper library mentioned above to call `escape()`.

### HTML

The filtering defaults to the HTML context, so no second attribute is needed:

```php
<?php echo escape('this is my <b>data</b>'); ?>
```

This would result in the string: `this is my &lt;b&gt;data&lt;/b&gt;`. This replacement performs an [htmlspecialchars](http://php.net/htmlspecialchars) call to filter the data. This is to be used for generic HTML escaping only.

### HTML Attribute

HTML attributes need a bit different handling as they're a different context. Below is a simple example of passing in the HTML attribute string for escaping:

```php
 <a href="" <?php echo escape('name="f\'test\'oo" onclick="error"', 'htmlattr'); ?>Sample Link</a>
 ```

The resulting string is `name="f&quot;test&quot;oo"` with the quotes transformed into their HTML entities. You'll also notice that the `onclick` attribute is missing from the output. This is one of several terms that are blacklisted as Javascript interaction should not happen in the HTML tag attributes. If you **really** need to override it, you can configure the escaping with an optional third parameter:

```php
<a href="" <?php echo escape('name="f\'test\'oo" onclick="error"', 'htmlattr', array('allow' => 'onclick')); ?>Sample Link</a>
```


### Javascript

Javascript escaping transforms characters into their entities and performs the replacement:

```php
<script>
var foo = "<?php echo escape('te"this"sting', 'js'); ?>";
</script>
```

The resulting output is `var foo = "te\x22this\x22sting";` having the quotes escaped correctly.

### CSS

CSS escaping transforms all non-alphanumeric characters into their entity versions. So:

```php
<div style="<?php echo escape("font-size:100px;color:red;font-weight:bold", 'css'); ?>">testing this</div>
```

results in `<div style="font&#45size&#58100px&#59color&#58red&#59font&#45weight&#58bold">testing this</div>` after being escaped.

### URL

This escapes data to be used safely in a URL string (not the entire URL, just the data to be used in the query string):

```php
<a href="/link.php?<?php echo escape(array('foo' => 'bar "this"'), 'url'); ?>">my link</a>
```

results in:

```html
<a href="/link.php?foo=bar+%22this%22">my link</a>
```

## Helper Methods

There's also helper methods as a part of the `BreakoutHelper` for each kind of escaping:

```php
<?php
escapeHtml(...);
escapeHtmlAttr(...);
escapeCss(...);
escapeJs(...);
escapeUrl(...);
?>
```

These all take in the same two options: the `$data` to escape and the `$config` array of additional options.

## Document Escaping

Breakout also has the ability to escape out a "document" (string that's provided) and replace placeholders with values with the requested escaping. For example:

```php
<?php

$data = array(
    'html1' => '<b>testing</b>',
    'js1' => 'te"this"sting',
    'css1' => 'font-size:100px;color:red;font-weight:bold'
);
$document = "this is a test of the document rendering.\n{{ js1|js }} and\n {{ css1|css }} finally\n {{ html1 }}";
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);

echo $result;
?>
```

This will echo out the result:

```
his is a test of the document rendering.
te\x22this\x22sting and
 font&#45size&#58100px&#59color&#58red&#59font&#45weight&#58bold finally
 &lt;b&gt;testing&lt;/b&gt;
```

Each item is escaped first according to the type following the pipe `|` character then replaced in the document. The result is returned back from the `render` call. **Note:** There is currently not a "helper" method for the `render` function.

> **NOTE:** Any tags that do not have matching data will **not be rendered and will just be removed**. No data replacement will happen.

## Document Escaping - Strings, Objects and Functions

The document escaping not only supports strings as seen in the above example but it also supports basic interactions with objects via properties and method calls. They work similarly to the string replacement but with a bit of extra syntax. To reference an object property or method, you use a period and optional parentheses to specify a method call. Here's some examples:

```php
<?php

class Object1 {
    public $property1 = 'test';

    public function myMethod()
    {
        return 'foobar';
    }
}

$data = [ 'obj1' => new Object1() ];

$document = "this should replace both the property {{ obj1.property1 }} and the method {{ obj1.myMethod() }} values.";
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);

/**
 * This example results in:
 * 'this should replace both the property test and the method foobar values.'
 */
?>
```

You can see in the output line that both of the object-related values are replaced. The first token shows using the period notation to define the (public) property to display and the second token uses the method call with the `()` denoting it's a method. If you don't include the parentheses it will assume it's a property.

Additionally, there's one more trick `Breakout` has under its sleeve. If the data passed in is an object and no property or methods are defined on it, the system will try to detect if a `__toString` method is defined. For example:

```php
<?php

class Object1 {
    public function __toString()
    {
        return 'my object';
    }
}

$data = [ 'obj1' => new Object1() ];

$document = "this should replace based on toString: {{ obj1 }}";
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);

/**
 * This example results in:
 * 'this should replace based on toString: my object'
 */
?>
```

## Delimiter Replacement

By default `Breakout` uses the `{{` and `}}` for the delimiters. If you happen to use something else, you can set them as an optional parameter on the `render` method call:

```php
<?php
// You can either set them as n array, one for each side
$result = \SalesforceEng\Breakout\Breakout::render($document, $data, array('%%', '%%'));

// Or if you just use a single delimiter for both side, you can just use a string:
$result = \SalesforceEng\Breakout\Breakout::render($document, $data, '%%');
?>
```

For this example replacing the "double percent" delimiter, instead of replacing `{{ foo }}` it would replace `%% foo %%`.

## Outputting raw data

There could be cases (like if you're using a frontend templating library that uses the same delimiters as Breakout) where you might want to output raw data with no formatting or interpolation changes. You can use the `raw` block to accomplish this:

```php
<?php
$document = <<<EOD
username: {{ username }}

{% raw %}
{{ this should remain }}
{% endraw %}
EOD;

$data = [ 'username' => 'ccornutt' ];
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);
?>
```

The resulting document will leave the `{{ this should remain }}` value alone and output it directly.

## Control Structures

In addition to basic variable replacement, Breakout also supports some basic control structures. Currently there are only two
supported: `for` and `if`.

### Using For

```php
<?php
$document = <<<EOD
{% for item in items %}
    item: {{ item }}
{% endfor %}
EOD;

$data = [
    'items' => ['foo', 'bar', 'baz']
];
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);
?>
```

This results in each of the items in the list being output like: `item: foo`.

### Using If

The `if` works much the same way and is essentially an `isset`:

```php
<?php
$document = <<<EOD
{% if item %}
    {{ item }}
{% endif %}
EOD;

$data = ['item' => 'foobar'];
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);
?>
```

In this case it would just output "foobar" as the `item` value exists. The `if` handling also provides simple evaluation handling:

```php
<?php
// With string matching
$document = "{% if user.name == 'user1' %} show this {% endif %}";

// Or booleans
$document = "{% if user.active == true %} show this {% endif %}";

// Or just checking if the value is set
$document = "{% if user.active %} show this {% endif %}";
?>
```

as well as "else" functionality:

```php
<?php
$document = "{% if user.name == 'user1' %} show this {% else %} show that {% endif %}";

?>
```

In the above example if `user.name` is equal to "user" it outputs "show this". If not, you'll get "show that". `Elseif` is currently not supported.

### Nesting Control Structures

You can also nest these control structures like `for` and `if`:

```php
<?php
$document = <<<EOD
{% for item in items %}
    {% if item.name %}{{ item.name }}{% endfor %}
{% endfor %}
EOD;

$data = [
    'items' => [
        ['name' => 'item1'],
        ['value' => 'item2']
    ]
];
$result = \SalesforceEng\Breakout\Breakout::render($document, $data);
?>
```

This would only output "item1" as the second item in the list has no `name` value (the tag is just removed if no data is present).
