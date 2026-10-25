`<!--{ SQL script that creates the tables of the model, then their foreign
     keys. One statement per line. }-->``[$tables]`CREATE TABLE `{$qname}`(`?$pk`PRIMARY KEY (`[$pk]``{$value}``!$_is_last`,`$_is_last!``[/$pk]`),`$pk?``[$fields]``{$name}` `{$sqlType}``?$varchar`(`{$long}`)`$varchar?``?$notNull``{\s}`NOT NULL`{\s}``$notNull?``?$unique``{\s}`UNIQUE`{\s}``$unique?``!$_is_last`,`{\s}``$_is_last!``[/$fields]`);
`[/$tables]``[$foreignKeys]``{\s}`ALTER TABLE `{$qname}` ADD FOREIGN KEY (`[$locals]``{$value}``!$_is_last`,`$_is_last!``[/$locals]`) REFERENCES `{$foreignQname}`(`[$foreigns]``{$value}``!$_is_last`,`$_is_last!``[/$foreigns]`)`{\s}``?$onDelete``{\s}`ON DELETE CASCADE`{\s}``$onDelete?``?$onUpdate``{\s}`ON UPDATE CASCADE`{\s}``$onUpdate?`;`{\s}`
`[/$foreignKeys]`
