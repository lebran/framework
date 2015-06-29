<?php
namespace Leaf\Di;

/**
 *
 * @author Roma
 */
interface InjectionInterface
{
    public function setDi(Container $di);

    public function getDi();
}