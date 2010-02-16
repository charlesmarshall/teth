require "yaml"
require "open-uri"

def fetch_repo(zip, localname)
  writeOut = open(localname, "wb")
  writeOut.write(open(zip).read)
  writeOut.close
end

def unzip(zip, dir)
  puts " unzipping #{zip}\n";
  `unzip #{zip} -d #{dir}`
end

def zip(filename, source)
  puts " zipping #{filename} from #{source}\n"
  `cd ./to_be_zipped/ && zip -r ../zips/#{filename}.zip ./#{source}`
end

def move(newname, dir)
  Dir.foreach("#{dir}") do |file|
    if File.directory?("#{dir}#{file}") && file != "." && file != ".."
      puts "  renaming #{file} to #{newname}\n"
      `mv ./dump/#{file} #{newname}`
    end
  end
end

def download(repos)
  puts "-- Downloading --\n"
  repos.each do |name, details|
    puts " -> #{name}\n"
    fetch_repo(details['zip'], "./dump/#{name}.zip")
  end
end

def make_skel(repos)
  puts "-- Making Skel --\n"
  skels = Array.new
  repos.each do |name, details|
    if details['skel'] == true
      skels.push(name);
      unzip("./dump/#{name}.zip", "./dump/")
      move("./to_be_zipped/#{name}","./dump/")
    end
  end
  return skels;
end

def add_core(skels, repos)
  core = false
  core_name = false
  repos.each do |name, details|
    if details['core'] == true 
      core = details
      core_name = name
    end
  end
  skels.each do |repo_name, details|
    unzip("./dump/#{core_name}.zip", "./dump/")
    move("./to_be_zipped/#{repo_name}/#{core['name']}","./dump/")
  end
  
end


def tidy()
  puts "-- DELETED TMP --"
  `rm -Rf ./dump && rm -Rf ./to_be_zipped`
end

config = YAML::load_file("./_config.yml");
repos = config['teth_repositories'].sort

`mkdir ./dump/ && chmod -Rf 0777 ./dump/`
`mkdir ./to_be_zipped/ && chmod -Rf 0777 ./to_be_zipped/`
`mkdir ./zips/ && chmod -Rf 0777 ./zips/`
download(repos)
skels = make_skel(repos).sort
add_core(skels, repos)

skels.each do |repo_name|
  puts "-- MAKING ZIPS --\n"
  filename = "#{repo_name}_teth_core"
  zip(filename, repo_name);
  repos.each do |name, details|
    if(details['skel'] != true && details['core'] != true)
      filename += "_#{name}"
      unzip("./dump/#{name}.zip", "./dump/")
      move("./to_be_zipped/#{repo_name}/plugins/#{name}","./dump/")
      zip(filename, repo_name);
    end
  end
end


tidy()