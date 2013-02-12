#!/bin/bash
# generate IncParser Files

DIR=`dirname "$0"`;
cd "$DIR";
BIN=../../../../bin;
BNF=./inc_grammar.bnf;

$BIN/lex $BNF --name=IncLex --tmin=500 1>../IncLex.php
$BIN/grammar $BNF --name=IncGrammar --tmin=500 1>../IncGrammar.php
$BIN/lrtable $BNF --name=IncParseTable --tmin=500 1>../IncParseTable.php







echo;
exit 0;

