<?php

namespace YakNet\AccessibilityConsole\Reporting;

use YakNet\AccessibilityConsole\Core\Violation;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class XmlFormatter implements FormatterInterface
{
    public function format(array $violations, int $baselinedCount, SymfonyStyle $io): int
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        
        $root = $dom->createElement('report');
        $root->setAttribute('totalViolations', (string)count($violations));
        $root->setAttribute('baselinedViolations', (string)$baselinedCount);
        $dom->appendChild($root);
        
        $violationsNode = $dom->createElement('violations');
        $root->appendChild($violationsNode);
        
        foreach ($violations as $v) {
            $node = $dom->createElement('violation');
            $node->setAttribute('ruleId', $v->ruleId);
            $node->setAttribute('severity', $v->severity->value);
            $node->setAttribute('standard', $v->standard->value);
            
            $msg = $dom->createElement('message', htmlspecialchars($v->message));
            $node->appendChild($msg);
            
            $file = $v->location['file'] ?? 'unknown';
            $line = $v->location['line'] ?? 0;
            
            $loc = $dom->createElement('location');
            $loc->setAttribute('file', $file);
            $loc->setAttribute('line', (string)$line);
            $node->appendChild($loc);
            
            $snippet = $dom->createElement('htmlSnippet');
            $snippet->appendChild($dom->createCDATASection($v->htmlSnippet));
            $node->appendChild($snippet);
            
            if ($v->fixSuggestion) {
                $sug = $dom->createElement('suggestion', htmlspecialchars($v->fixSuggestion));
                $node->appendChild($sug);
            }
            
            $violationsNode->appendChild($node);
        }
        
        $io->write($dom->saveXML() ?: '');
        
        return empty($violations) ? Command::SUCCESS : Command::FAILURE;
    }
}
