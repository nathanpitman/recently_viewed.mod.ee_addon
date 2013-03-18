Recently Viewed Entries for ExpressionEngine
============================================
Original EE1 module by <a href="http://github.com/ninefour">ninefour</a>. Updated for EE2 by <a href="http://github.com/agathongroup">agathongroup</a>.

The "Recently Viewed Entries" module allows you to keep a record of recently viewed entries on a per member basis and display that data back to the user in your page templates. The module uses the members session ID so they need not be logged in.

Usage example:

Record views for entries 1-5:

```c
{exp:recently_viewed:add_entry channel="channel_name" entry_id="1"}
{exp:recently_viewed:add_entry channel="channel_name" entry_id="2"}
...
```

Retrieve recently viewed entries:
```c
{exp:recently_viewed:get_entries channel="channel_name" distinct="yes"} // EE1: distinct="on"
```

Will output:

```c
1|2|3|4|5
```

The EE2 version of this module also lets you set a `limit` parameter on `{exp:recently_viewed:add_entry}`, to control how many entries are stored in the database. The default number is 5. The EE1 module will store a maximum of 5 entries.

```c
{exp:recently_viewed:add_entry channel="channel_name" entry_id="{entry_id}" limit="10"}
```
