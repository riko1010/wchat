# -*- coding: utf-8 -*-


from cleo import Command

from clevercsv.wrappers import detect_dialect
from ._utils import parse_int


class DetectCommand(Command):
    """
    Detect the dialect of a CSV file

    detect
        { path : The path to the CSV file }
        { --c|consistency : Use only the consistency measure for detection }
        { --e|encoding= : Set the encoding of the CSV file }
        { --n|num-chars= : Limit the number of characters to read for
        detection. This will speed up detection but may reduce accuracy. }
        { --p|plain : Print the components of the dialect on separate lines. }
        { --j|json : Print the components of the dialect as a JSON object. }
    """

    help = "The <info>detect</info> command detects the dialect of a given CSV file."

    def handle(self):
        verbose = self.io.verbosity > 0
        num_chars = parse_int(self.option("num-chars"), "num-chars")
        method = 'consistency' if self.option('consistency') else 'auto'
        dialect = detect_dialect(
            self.argument("path"),
            num_chars=num_chars,
            encoding=self.option("encoding"),
            verbose=verbose,
            method=method
        )
        if dialect is None:
            return self.line("Dialect detection failed.")
        if self.option("plain"):
            self.line(f"delimiter = {dialect.delimiter}".strip())
            self.line(f"quotechar = {dialect.quotechar}".strip())
            self.line(f"escapechar = {dialect.escapechar}".strip())
        elif self.option("json"):
            self.line(dialect.serialize())
        else:
            self.line("Detected: " + str(dialect))
