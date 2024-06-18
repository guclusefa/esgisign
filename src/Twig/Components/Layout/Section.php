<?php

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Section
{
    public string $title = '';
    public string $description = '';
}