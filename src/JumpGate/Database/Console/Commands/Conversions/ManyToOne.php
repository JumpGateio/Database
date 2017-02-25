<?php

namespace JumpGate\Database\Console\Commands\Conversions;

abstract class ManyToOne extends BaseCommand
{
    protected $tables = [];

    protected $phoneNumbers = [];

    protected $socials = [];

    protected $phoneFields = [];

    protected $socialFields = [];

    protected $clearTables = [];

    protected $linkIndex = 1;

    protected $links = [];

    abstract protected function convertResource($resource, $table, $type, $key, $typeTable, $typeKey);

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->clearAllData();

        $this->setUp();

        foreach ($this->tables as $table) {
            $this->handleBatch(
                $table['table'],
                $table['morphClass'],
                $table['morphField'],
                $table['typeTable'],
                $table['typeId']
            );
        }

        $this->handleExtras();
    }

    protected function clearAllData()
    {
        $this->clearData($this->getNewTable(), $this->getNewTable());

        if (! empty($this->clearTables)) {
            foreach ($this->clearTables as $table) {
                $this->clearData($table);
            }
        }
    }

    protected function setUp()
    {
        //
    }

    protected function addExtras($resource)
    {
        //
    }

    protected function handleExtras()
    {
        //
    }

    protected function loopExtras()
    {
        //
    }

    protected function addTable($table, $morphClass = null, $morphField = null, $typeTable = null, $typeId = null)
    {
        $this->tables[] = compact('table', 'morphClass', 'morphField', 'typeTable', 'typeId');
    }

    protected function addLink($table, $id)
    {
        $this->links[$this->linkIndex] = compact('table', 'id');

        $this->linkIndex++;
    }

    protected function handleBatch($table, $class, $key, $typeTable, $typeKey)
    {
        $min       = 001;
        $max       = $this->batchSize;
        $fullCount = $this->db
            ->connection('mysql_old')
            ->table($table)
            ->count();

        $this->db
            ->connection('mysql_old')
            ->table($table)
            ->orderBy('id', 'asc')
            ->chunk($this->batchSize, function ($resources) use ($table, $class, $key, $typeTable, $typeKey, &$min, &$max, $fullCount) {
                $this->startBar(
                    $min . '-' . $max . '/' . $fullCount . ': ' . $table,
                    $this->getNewTable(),
                    $resources
                );

                $resources = collect($resources)->map(function ($resource) use ($table, $class, $key, $typeTable, $typeKey) {
                    $this->advanceBar();

                    $this->addExtras($resource);

                    return $this->convertResource($resource, $table, $class, $key, $typeTable, $typeKey);
                })->filter()->toArray();

                $this->db->table('phone_numbers')->insert(array_filter($this->phoneNumbers));
                $this->db->table('socials')->insert(array_filter($this->socials));

                $this->db->table($this->getNewTable())->insert($resources);

                $this->finishBar();

                $this->runLinks();

                $this->loopExtras();

                $min += $this->batchSize;
                $max += $this->batchSize;
            });
    }

    private function runLinks()
    {
        $min       = 001;
        $max       = $this->batchSize;
        $fullCount = count($this->links);

        collect($this->links)
            ->chunk($this->batchSize)
            ->each(function ($links) use (&$min, &$max, $fullCount) {
                $this->startBar(
                    $min . '-' . $max . '/' . $fullCount,
                    'table_links',
                    $links
                );

                $resources = $links->map(function ($link, $index) {
                    $this->advanceBar();

                    return [
                        'original_table' => $link['table'],
                        'original_id'    => $link['id'],
                        'new_table'      => $this->getNewTable(),
                        'new_id'         => $index,
                    ];
                })->toArray();

                $this->db->table('table_links')->insert($resources);

                $this->finishBar();

                $min += $this->batchSize;
                $max += $this->batchSize;
            });

        $this->links = [];
    }
}
