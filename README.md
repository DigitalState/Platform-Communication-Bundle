# Platform-Communication-Bundle




### FAQ

##### How do i add custom entities to the "Entity" droptown in the Communication and Template pages ?

Simply add a `@Config` Annotation with the `contact_information` attribute. See `OroCRM\Bundle\ContactBundle\EntityContact` class for an example

```php

namespace OroCRM\Bundle\ContactBundle\Entity;

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


