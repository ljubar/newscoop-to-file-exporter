Console command usage:

```$bash
newscoop:import-and-save-to-html [options] [--] <domain> [<start>] [<end>]

Arguments:
  domain                         Newscoop instance domain to fetch data from it.
  start                          Number of article (start import from it). [default: 1]
  end                            Number of article (stop import on it). [default: 100]

Options:
      --force-image-download     Re-download images even if they are already fetched
      --print-rendered-template  Prints result of template rendering
```

example:

```php bin/console newscoop:import-and-save-to-html 'http://24wspolnota.pl' 26527 26527 --force-image-download --print-rendered-template```