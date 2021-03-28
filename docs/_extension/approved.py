import pprint
import re
from typing import List

from docutils import nodes
from docutils.nodes import Element, Node
from docutils.parsers.rst import directives
from sphinx.directives.code import LiteralIncludeReader, dedent_lines
from sphinx.util.docutils import SphinxDirective

class approved(nodes.General, nodes.Element):
    pass

class CodeImportDirective(SphinxDirective):
    has_content = False
    required_arguments = 1
    optional_arguments = 0
    final_argument_whitespace = True
    option_spec = {
        'language': directives.unchanged_required,
        'section': directives.unchanged_required,
    }

    def run(self) -> List[Node]:
        document = self.state.document

        try:
            location = self.state_machine.get_source_and_line(self.lineno)
            rel_filename, filename = self.env.relfn2path(self.arguments[0])
            self.env.note_dependency(rel_filename)
            reader = LiteralIncludeReader(filename, self.options, self.config)
            text, lines = reader.read(location=location)
            text = text.split("\n---\n")[int(self.options['section'])]

            retnode = nodes.literal_block(text, text, source=filename, language=self.options['language'])  # type: Element

            return [retnode]
        except Exception as exc:
            return [document.reporter.warning(exc, line=self.lineno)]

    def filter(self, lines: List[str], sections: List[str]) -> List[str]:
        sectionLines = []
        parsing = None
        for line in lines:
            if frozenset(sections) & frozenset(self.parseSections("endsection", line)):
                parsing = False
            
            if frozenset(sections) & frozenset(self.parseSections("section", line)):
                parsing = True
                continue

            if parsing:
                sectionLines.append(line)

        if True == parsing:
            raise ValueError('No closing section found for %s' % (sections))

        if None == parsing:
            raise ValueError('Section %s not found' % (sections))

        return sectionLines;

    def parseSections(self, sectionType: str, line: str) -> List[str]:
        p = re.compile(".* %s: ([a-zA-Z_,]+)" % (sectionType))
        match = p.match(line)
        if None == match:
            return []

        match = match.groups()[0]

        return match.split(',')

    def filterSections(self, lines: List[str]) -> List[str]:
        sectionLines = []
        for line in lines:
            if "section: " in line:
                continue

            sectionLines.append(line)

        return sectionLines;

    def removeOpeningTag(self, lines: List[str]) -> List[str]:
        if lines[0].startswith('<?php'):
            return lines[1:]

        return lines

def setup(app):
    app.add_config_value('approved_include_approveds', False, 'html')
    app.add_directive('approved', CodeImportDirective)

    return {
        'version': '0.1',
        'parallel_read_safe': True,
        'parallel_write_safe': True,
    }
