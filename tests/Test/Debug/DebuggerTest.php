<?php

namespace Test\Debug;

use Neutrino\Constants\Services;
use Neutrino\Debug\DebugEventsManagerWrapper;
use Neutrino\Debug\Debugger;
use Neutrino\Debug\Reflexion;
use Phalcon\Db\Profiler;
use Phalcon\Events\Manager;
use Test\TestCase\TestCase;

class DebuggerTest extends TestCase
{
    public function tearDown()
    {
        Reflexion::set(Debugger::class, 'viewProfiles', null);
        Reflexion::set(Debugger::class, 'profilers', null);
        Reflexion::set(Debugger::class, 'instance', null);
        Reflexion::set(Debugger::class, 'view', null);

        parent::tearDown();
    }

    public function testRegisterGlobalEventManager()
    {
        $debugger = Reflexion::getReflectionClass(Debugger::class)->newInstanceWithoutConstructor();

        Reflexion::set($this->getDI(), '_eventsManager', $dim = new Manager());
        $this->getDI()->set(Services::EVENTS_MANAGER,$em = new Manager());
        Reflexion::set($this->app, '_eventsManager', $appm = new Manager());

        Reflexion::invoke($debugger, 'registerGlobalEventManager');

        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->getDI()->getInternalEventsManager());
        $this->assertEquals($dim, Reflexion::get($this->getDI()->getInternalEventsManager(), 'manager'));
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->getDI()->get(Services::EVENTS_MANAGER));
        $this->assertEquals($em, Reflexion::get($this->getDI()->get(Services::EVENTS_MANAGER), 'manager'));
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->app->getEventsManager());
        $this->assertEquals($appm, Reflexion::get($this->app->getEventsManager(), 'manager'));

        $this->getDI()->remove(Services::EVENTS_MANAGER);
        Reflexion::set($this->getDI(), '_eventsManager', null);
        Reflexion::set($this->app, '_eventsManager', null);

        Reflexion::invoke($debugger, 'registerGlobalEventManager');

        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->getDI()->getInternalEventsManager());
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->getDI()->get(Services::EVENTS_MANAGER));
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $this->app->getEventsManager());
    }

    public function testRegisterProfiler()
    {
        $profiler = Reflexion::invoke(Debugger::class, 'registerProfiler', 'db');

        $this->assertInstanceOf(Profiler::class, $profiler);

        $profiler2 = Reflexion::invoke(Debugger::class, 'registerProfiler', 'db');

        $this->assertEquals($profiler, $profiler2);
    }

    public function testAttachEventsManager()
    {
        $debugger = Reflexion::getReflectionClass(Debugger::class)->newInstanceWithoutConstructor();

        Reflexion::invoke($debugger, 'registerGlobalEventManager');

        Reflexion::invoke($debugger, 'tryAttachEventsManager', '');

        $dispatcher = $this->getDI()->get(Services::DISPATCHER);
        Reflexion::set($dispatcher, '_eventsManager', null);
        Reflexion::invoke($debugger, 'tryAttachEventsManager', $dispatcher);
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $dispatcher->getEventsManager());

        Reflexion::set($dispatcher, '_eventsManager', $em = new Manager());
        Reflexion::invoke($debugger, 'tryAttachEventsManager', $dispatcher);
        $this->assertInstanceOf(DebugEventsManagerWrapper::class, $dispatcher->getEventsManager());
        $this->assertEquals($em, Reflexion::get($dispatcher->getEventsManager(), 'manager'));
    }
}
