<?php

namespace JumpGate\Database\Console\Commands\Conversions;

abstract class OneToOne extends Conversion
{
    protected $phoneNumbers = [];

    protected $socials = [];

    protected $phoneFields = [];

    protected $socialFields = [];

    protected $clearTables = [];

    abstract protected function convertResource($resource);

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->clearAllData();

        $this->setUp();

        $this->handleBatch();
    }

    protected function clearAllData()
    {
        $this->clearData($this->getNewTable());

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

    protected function handleBatch()
    {
        $min       = 001;
        $max       = $this->batchSize;
        $fullCount = $this->db
            ->connection('mysql_old')
            ->table($this->getOriginalTable())
            ->count();

        $this->db
            ->connection('mysql_old')
            ->table($this->getOriginalTable())
            ->orderBy('id', 'asc')
            ->chunk($this->batchSize, function ($resources) use (&$min, &$max, $fullCount) {
                $this->startBar(
                    $min . '-' . $max . '/' . $fullCount . ': ' . $this->getOriginalTable(),
                    $this->getNewTable(),
                    $resources
                );

                $resources = collect($resources)->map(function ($resource) {
                    $this->advanceBar();

                    $this->addExtras($resource);

                    return $this->convertResource($resource);
                })->filter()->toArray();

                $this->db->table('phone_numbers')->insert(array_filter($this->phoneNumbers));
                $this->db->table('socials')->insert(array_filter($this->socials));

                $this->db->table($this->getNewTable())->insert($resources);

                $this->finishBar();

                $min += $this->batchSize;
                $max += $this->batchSize;
            });
    }
}
