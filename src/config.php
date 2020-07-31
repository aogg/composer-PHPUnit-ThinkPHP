<?php

\think\Container::getInstance()->make(\think\App::class)->console->addCommand(\aogg\UnitCommand::class);
