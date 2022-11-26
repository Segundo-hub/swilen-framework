<?php

use Swilen\Container\Container;

uses()->group('Container');

beforeEach(function () {
    $this->container = new Container();
});

afterEach(function () {
    unset($this->constainer);
});

it('Tagging concrete implementations', function () {
    $this->container->tag([MemoryReportTaggedStub::class, CpuReportTaggedStub::class], PerformaceReportStub::class);

    /** @var \ArrayIterator<PerformaceReportStub> */
    $tagged = $this->container->tagged(PerformaceReportStub::class);

    expect($tagged)->toBeIterable();

    $results = [];
    foreach ($tagged as $key => $value) {
        $results[$key] = $value;
    }

    expect($tagged->count())->toBe(2);
    expect($results[0])->toBeInstanceOf(PerformaceReportStub::class);
    expect($results[0])->toBeInstanceOf(MemoryReportTaggedStub::class);
    expect($results[1])->toBeInstanceOf(CpuReportTaggedStub::class);

    $reports = [];
    foreach ($tagged as $key => $value) {
        $reports[$key] = $value->report();
    }

    expect($reports)->toBe([20, 60]);
});

it('Empty when is not found tag into container', function () {
    expect($this->container->tagged('not-found'))->toBeEmpty();
});

interface PerformaceReportStub
{
    public function report();
}

class MemoryReportTaggedStub implements PerformaceReportStub
{
    public function report()
    {
        return 20;
    }
}

class CpuReportTaggedStub implements PerformaceReportStub
{
    public function report()
    {
        return 60;
    }
}
