require "yaml"
require "open-uri"

def fetch_repo(zip, localname)
  writeOut = open(localname, "wb")
  writeOut.write(open(zip).read)
  writeOut.close
end

def unzip(zip, dir)
  `unzip #{zip} -d #{dir}`
end

def download(repos)
  puts "-- Downloading --\n"
  repos.each do |name, details|
    puts " -> #{name}\n"
    fetch_repo(details['zip'], "./dump/#{name}.zip")  
  end
end

config = YAML::load_file("./_config.yml");
repos = config['teth_repositories']

download(repos)
