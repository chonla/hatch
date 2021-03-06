# Hatch

Entities Driven RESTful API builder.

## Behind Hatch

* Hatch creates API on top of Slim framework.

## What does Hatch do?

Hatch compiles a configuration file, call Egg, and generates a result into a path.

## Egg

Egg is entities list telling Hatch what entity, so called Egglet, to be created.

### Egglet data type mapping

| Type            | DB Type       | Description           |
| --------------- | ------------  | --------------------- |
| very_short_text | varchar(64)   | -                     |
| short_text      | varchar(128)  | -                     |
| medium_text     | varchar(256)  | -                     |
| long_text       | varchar(1024) | -                     |
| very_long_text  | text          | -                     |
| datetime        | int           | -                     |
| date            | int           | -                     |
| auto            | int           | Autoincrement integer |

### Egglet special data type

| Type       | DB Type     | Description  |
| ---------- | ----------- | ------------ |
| files      | varchar(32) | Type "files" will create a relation mapping table between entity id and corresponding attribute |
| tags       | varchar(50) | Type "tags" will create a relation mapping table between entity id and corresponding attribute |
| password   | varchar(128) | Password generated by password_hash() function |

### Egglet default attributes

Hatch will automatically put following attributes to each egg.

| Attribute    | Type     |
| ------------ | -------- |
| id           | auto     |
| created_time | datetime |
| updated_time | datetime |

### Egg file example

```json
{
    "entities" : {
        "person" : {
            "name" : "short_text",
            "bio" : "long_text",
            "photo" : "files"
        }
    }
}
```

The above egg gives results of 2 tables:

Table : **person**

| Name         | Type         | Autoincrement |
| ------------ | ------------ | :-----------: |
| id           | int          | Yes           |
| name         | varchar(256) | -             |
| bio          | text         | -             |
| created_time | int          | No            |
| updated_time | int          | No            |

Table : **person_photo**

| Name  | Type        | Autoincrement |
| ----- | ----------- | :-----------: |
| id    | int         | No            |
| photo | varchar(32) | -             |

### Egg rendering

Most of Egg data type will be rendered as it is. Except the following data types will be rendered with following format:

| Type     | Filter                                     |
| -------- | ------------------------------------------ |
| date     | Date only : 2006-02-14                     |
| datetime | Date time : 2006-02-14T15:02:12 (ISO 8601) |

### Egglet attribute

Egglet can be customized by adding options to ```@``` attribute.

### Egglet attribute example

```json
{
    "database" : {
        "dsn" : "sqlite:./db.sq3"
    },
    "entities" : {
        "@" : {
            "auto" : true,
            "private" : [
                "citizen_id"
            ]
        },
        "person" : {
            "name" : "short_text",
            "bio" : "long_text",
            "citizen_id" : "very_short_text"
        }
    }
}
```

### Egglet attribute options

| Name    | Description                                                                   |
| ------- | ----------------------------------------------------------------------------- |
| auto    | (Default: true) If true, auto attributes will be added.                       |
| private | (Default: [] ) If set, API will not return field names specified in the list. |

## Data source name

Datasource name can be defined in Egg file in ```dsn``` key under ```database``` section.

### Data source name example

```json
{
    "database" : {
        "dsn" : "sqlite:./db.sq3"
    },
    "entities" : {
        "person" : {
            "name" : "short_text",
            "bio" : "long_text"
        }
    }
}
```

## Output directory

Output directory stores compiled content. It can be set under ```compiled``` section.

```json
{
    "compiled" : "./compiled",
    "database" : {
        "dsn" : "sqlite:./db.sq3"
    },
    "entities" : {
        "person" : {
            "name" : "short_text",
            "bio" : "long_text"
        }
    }
}
```

## Migration

Hatch offers data preparation in migration section in Egg file.

### Migration example

```json
{
    "compiled" : "./compiled",
    "database" : {
        "dsn" : "sqlite:./db.sq3"
    },
    "entities" : {
        "person" : {
            "name" : "short_text",
            "bio" : "long_text"
        }
    },
    "migration" : {
        "person" : [
            {
                "name" : "Adam Smith",
                "bio" : "Lecturer from Bangkok"
            },
            {
                "name" : "Martin Louie",
                "bio" : "Marathoner from Laos"
            }
        ]
    }
}
```

## CORS Support

Hatch, by default, grants requests from any origin. However, you can configure CORS setting in Egg before running Hatch or do it later in compiled output in ```src/middlewares.php```.

## Hatch result

As mentioned above, Hatch compiles Egg into ready-to-use RESTful API service. You just copy everything in output folder to your production.

## Troubleshooting

If you have error with getting composer, visit https://getcomposer.org/doc/articles/troubleshooting.md for detail.

## License

Hatch and all corresponding content are released under [MIT License](https://github.com/chonla/hatch/blob/master/LICENSE).