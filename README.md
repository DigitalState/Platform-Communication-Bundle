# Platform-Communication-Bundle

The Communication bundle provides business users the ability to manage government communications. It introduces six new entities to the system: [Channel](Entity/Channel.php), [Communication](Entity/Communication.php), [Content](Entity/Content.php), [Criterion](Entity/Criterion.php), [Message](Entity/Message.php) and [Template](Entity/Template.php).

## Table of Contents

- [Channel Entity](#channel-entity)
- [Communication Entity](#communication-entity)
- [Content Entity](#content-entity)
- [Criterion Entity](#criterion-entity)
- [Message Entity](#message-entity)
- [Template Entity](#template-entity)
- [Todo](#todo)
- [FAQ](#FAQ)

## Channel Entity

## Communication Entity

## Content Entity

## Criterion Entity

## Message Entity

## Template Entity

## Todo




## FAQ

##### How do i add custom entities to the "Entity" droptown in the Communication and Template pages ?

Simply add a `@Config` Annotation with the `contact_information` attribute. See `Oro\Bundle\ContactBundle\EntityContact` class for an example

```php

namespace Oro\Bundle\ContactBundle\Entity;

/**
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-group",
 *              "contact_information"={
 *                  "email"={
 *                      {"fieldName"="primaryEmail"}
 *                  },
 *                  "phone"={
 *                      {"fieldName"="primaryPhone"}
 *                  }
 *              }
 *          },
 *      }
 * )
 */
 class Contact extends ExtendContact implements EmailOwnerInterface
{
}
```

##### How can i make attributes from my class accessible to the Communication bundle ?

The communication bundle look for the `contact_information` and also for the following interfaces

Mandatory : 
- EmailOwnerInterface
- FirstNameInterface
- LastNameInterface

Facultatives : 
- FullNameInterface
- NamePrefixInterface
- MiddleNameInterface
- NameSuffixInterface




##### How do i add new search Criterias in the Query Builder ? 
Look for existing example of classes that hinerit `interface VirtualFieldProviderInterface
` and also services tagged with `oro_entity.virtual_field_provider`


