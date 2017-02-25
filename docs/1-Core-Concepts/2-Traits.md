# Traits

## HasUniqueColumns

> Make sure you add the following properties to your model.
```
    /**
     * Any field in this array will be populated with a unique string on create.
     *
     * @var array
     */
    protected static $uniqueStringColumns = [];

    /**
     * The size string to generate for unique string column.
     *
     * @var int
     */
    protected static $uniqueStringLimit = 10;
```
