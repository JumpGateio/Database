# Searching

- [Setting it up](#setting-it-up)

## Setting it up

1. Implment Searchable on your model.
1. Include the CanSearch Trait.
1. Set your search provider class name on `$searchProvider`
    - If you don't need a custom one use `\JumpGate\Database\Searching\Providers\Search::class`
