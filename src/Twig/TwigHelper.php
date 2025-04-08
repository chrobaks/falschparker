<?php

namespace App\Twig;

use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigHelper extends AbstractExtension
{

    private string $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        // Setze das Projektverzeichnis, um Dateien im "public"-Ordner zu finden
        $this->projectDir = $kernel->getProjectDir();
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSvgUrl', [$this, 'getSvgUrl']),
            new TwigFunction('decodeBase64Svg', [$this, 'decodeBase64Svg']),
        ];
    }

    function decodeBase64Svg(string $base64Url): string
    {
        // Entferne den Präfix "data:image/svg+xml;base64,"
        $base64String = preg_replace('/^data:image\/svg\+xml;base64,/', '', $base64Url);

        // Base64-String dekodieren
        $svgContent = base64_decode($base64String);

        if ($svgContent === false) {
            throw new \RuntimeException("Fehler beim Dekodieren der Base64 SVG-Daten.");
        }

        return $svgContent;
    }
    public function getSvgUrl(string $svgPath, string $color = 'currentColor'): string
    {
        $svgPath = $this->projectDir . '/public' . str_replace('assets', 'build',$svgPath);
        $svgContent = file_get_contents($svgPath);

        if ($svgContent === false) {
            throw new \RuntimeException("SVG-Datei konnte nicht geladen werden: $svgPath");
        }

//        $updatedSvg = preg_replace(
//            '/<svg([^>]*)>/',
//            '<svg$1 style="fill:' . $color . '">',
//            $svgContent
//        );


        $updatedSvg = preg_replace('/fill="[^"]*"/', 'fill="'.$color.'"', $svgContent);

        if ($updatedSvg === null) {
            throw new \RuntimeException("SVG-Datei konnte nicht angepasst werden.");
        }

        $base64Svg = base64_encode($updatedSvg);

        // Erzeuge die vollständige Data-URL
        return 'data:image/svg+xml;base64,' . $base64Svg;
    }
}