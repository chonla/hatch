# Hatch

Entities Driven RESTful API builder.

## Behind Hatch

* Hatch creates API on top of Slim framework.

## What Hatch does?

Hatch compiles a configuration file, call Egg, and generates a result into a path.

## Egg

Egg is entities list telling Hatch what entity, so called Egglet, to be created.

### Egg data type mapping

| Type       | DB Type      | Description           |
| ---------- | ------------ | --------------------- |
| short_text | varchar(256) | -                     |
| long_text  | text         | -                     |
| datetime   | int          | -                     |
| date       | int          | -                     |
| auto       | int          | Autoincrement integer |

### Egg special data type

| Type       | DB Type     | Description  |
| ---------- | ----------- | ------------ |
| files      | varchar(32) | Type "files" will create a relation mapping table between entity id and corresponding attribute |
| tags       | varchar(50) | Type "tags" will create a relation mapping table between entity id and corresponding attribute |

### Egg default attributes

Hatch will automatically put following attributes to each egg.

| Attribute    | Type     |
| ------------ | -------- |
| id           | auto     |
| created_time | datetime |
| updated_time | datetime |

### Egg example

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

The above egg will results of 2 tables:

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

| Type     | Filter                                   |
| -------- | ---------------------------------------- |
| date     | Date only : 2006-02-14                   |
| datetime | Date time : 2006-02-14T15:02:12          |
| files    | Url to download file : http://domain/... |

## Hatch result

As mentioned above, Hatch compiles Egg into ready-to-use RESTful API service. You just copy everything in output folder to your production.
