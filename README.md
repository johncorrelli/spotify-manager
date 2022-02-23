# spotify-manager

If you're like me, you can't stand that you can't thumbs down artists (or even songs) on Spotify Premium. Using this little tool, you can add a blacklist of artists and songs that the manager will skip when they are playing.

This is still a WIP/Proof of Concept. I have plans to rework a good bit of how the blacklist is generated and used.

## Step 1
- Clone the repo to your local machine.
- Run `composer install`

## Step 2: API Authentication
- Head over to Spotify and create an [application](https://developer.spotify.com/documentation/general/guides/app-settings/#register-your-app)
  - Create a new application
  - You are not creating a commercial integration
  - Note your `Client ID` and `Client Secret`

## Step 3: Running the script

Running the script is as simple as running `./run` from the root directory of the repo.

### For the first time:

Running this for the first time will take a few extra seconds of setup, as we'll need to enter the three values we generated in Step 2.

Those values are:
  - `Client ID`
  - `Client Secret`
  - `OAuth Code`

Once you've entered these values, they'll be stored in a git ignored file at `storage/auth.json` and you won't need to enter them again.

## Usage

- `./run` to have `spotify-manager` watch your currently playing track and let you hear what you want to hear and skip what you don't!
- `./run block-song` to never hear that song again
- `./run block-artist` to never hear a song from that artist again
  - in the case of songs with multiple artists, only the first artist is blocked.
- `./run block-album` to never hear a song from that album again

