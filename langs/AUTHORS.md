# Translation authors

Every language file may carry an optional `"_author"` key — the name shown here.
If you add or fix a translation, put yourself in it and update this table.

| File | Language | Author |
| --- | --- | --- |
| `en.json` | English | Danil "yuno" Zhuzhich |
| `ru.json` | Русский | Danil "yuno" Zhuzhich |
| `es.json` | Español | OpenSpeed maintainers (AI-assisted) |
| `de.json` | Deutsch | OpenSpeed maintainers (AI-assisted) |
| `fr.json` | Français | OpenSpeed maintainers (AI-assisted) |
| `pl.json` | Polski | OpenSpeed maintainers (AI-assisted) |

## Adding a language

1. Copy `langs/en.json` to `langs/<code>.json` (ISO 639-1: `it`, `pt`, `uk`, `tr` …).
2. Set `"_name"` to the native name of the language and `"_author"` to yourself.
3. Translate the values, keep the keys untouched.
4. Open a pull request — the language shows up in the switcher by itself.
