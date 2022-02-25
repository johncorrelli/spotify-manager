## Soon

- [ ] handle token refresh while still running
- [ ] refactor player controls out of `PlayerWater.php`
- [ ] don't immediately exit when player is not playing
  - add some fall back so that it doesn't stop running when player is paused.
  - or when player is playing a podcast
- [ ] refactor commands to not all live inside of `run.php`

## Future

- [ ] Integrate with google calendar to lower the volume of your spotify player when a event is about to start
  - perhaps in the interim add commands to modify player volume
