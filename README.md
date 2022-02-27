# spotify-manager

If you're like me, you can't stand that you can't thumbs down artists (or even songs) on Spotify Premium. Using this little tool, you can add a blacklist of artists, albums, and songs that the manager will skip when they are playing.

This is still a WIP/Proof of Concept.

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
  - while `spotify-manager` is running, it will give you the options to block the current:
    - song
    - artist
      - in the case of a song with multiple artists, at this time, only the first artist is blocked.
    - album
