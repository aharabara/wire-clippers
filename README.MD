 - [x] Class generator
 - [x] REPL
 - [ ] add namespaces with `pushn` and `popn`
 - [ ] add preview command with tree-like output
 - [ ] add `chain <interface>` to wrap a group of classes with `interface` into a single facade
 - [ ] add transformer generator with commands like: `transformer <alias/class> <alias/class>`
    - [ ] ask which folders to load
    - [ ] ask which class to use (from and to)
    - [ ] ask which transformation will be used 
        -> getters to props
        -> getters to instantiation
        -> getters to setters
    - [ ] show fields that were autowired and ask for help if required 
 
 
```
.user[name, password]
#mapper[toDTO, toEntity, supports]
.chain-mapper#mapper
.extendedPDO>PDO
.
```

```
.user[name:string, email:(.email[address:string]), password:(.password[value:string])]

```