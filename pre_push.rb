#!/usr/bin/ruby

require "yaml"
require "open-uri"

def fetch_repo(zip, localname)
  writeOut = open(localname, "wb")
  writeOut.write(open(zip).read)
  writeOut.close
end

def unzip(zip, dir)
  `unzip #{dir}#{zip} -d #{dir}`
end

def zip(start_dir, dest_dir, zipname, source)
  `cd #{start_dir} && zip -r #{dest_dir}#{zipname}.zip #{source}`
end

def move_all_directories(from, to)
  Dir.foreach("#{from}") do |file|
    if File.directory?("#{from}#{file}") && file != "." && file != ".."
      `mv #{from}#{file} #{to}`
    end
  end
end

def download(repos)
  repos.each do |name, details|
    puts "  #{name}\n"
    fetch_repo(details['zip'], "./dump/#{name}.zip")
  end
end

#http://javazquez.com/juan/2009/09/16/generating-combinations-with-ruby-and-recursion/
def combination(ary,head=[])
  return ary if(ary==[] )
  head << ary.shift
  tail = ary.clone
  tmp=[]
 if(tail.length>=1)
    tmp+= combination(tail[1..(tail.length)],head.flatten)+
                       combination(Array(tail[-1]),head.flatten)
    tmp+=combination(tail[2..(tail.length)],head.flatten) if(tail[2])
  else
    tmp += combination(tail,Array(head[0]))
  end
  tmp += combination(tail, []) +combination(ary,head.flatten)
  return (tmp << head).uniq
end


def tidy(dir)
  `rm -Rf #{dir}`
end

def make_package(path, modules, dumpdir)
  puts "      making package\n"
  modules.each do |name, info|
    puts "       extracting zip\n"
    unzip("#{name}.zip", "#{dumpdir}")
    puts "       moving to #{path}#{name}\n"
    move_all_directories(dumpdir, "#{path}#{name}")
  end
end

config = YAML::load_file("./_config.yml");
repos = config['teth_repositories'].sort

dump_dir = "./dump/"
package_dir = "./to_be_packaged/"
zip_dir = "zips/"

puts "-- creating temporary directories\n"
`mkdir #{dump_dir} && chmod -Rf 0777 #{dump_dir}`
`mkdir #{package_dir} && chmod -Rf 0777 #{package_dir}`
`mkdir #{zip_dir} && chmod -Rf 0777 #{zip_dir}`

puts "-- downloading\n"
download(repos)

skels = {}
cores = {}
plugins = {}

puts "-- segregating repos\n"
repos.each do |repo_name, repo_info|
  if repo_info['skel'] == true
    skels[repo_name] = repo_info
  elsif repo_info['core'] == true
    cores[repo_name] = repo_info
  else
    plugins[repo_name] = repo_info
  end
end

puts "-- starting on skels\n"
skels.each do|skel_name, skel_info|
  puts "-- #{skel_name}\n"
  base_name = "#{skel_name}"
  #unpack the skel
  puts "  extracting zip...\n"
  unzip("#{skel_name}.zip", dump_dir)
  #move them
  puts "  moving to working directory\n"
  move_all_directories(dump_dir, "#{package_dir}#{skel_name}")
  puts "  adding cores..\n"
  #unpack all the cores in to the skel
  cores.each do |core_name, core_info|
    puts "  -- #{core_name}\n"
    base_name += "_#{core_name}"
    puts "      extracting zip..\n"
    unzip("#{core_name}.zip", dump_dir)
    puts "      moving to #{skel_name}\n"
    move_all_directories(dump_dir, "#{package_dir}#{skel_name}/#{core_name}")
  end
  puts "  figuring out combinations..\n"
  #lets prep some arrays so can run that mad combination function
  values = []
  keys = []
  plugins.each{|name,info| values.push(name)}
  for x in 0..values.length-1 do 
    keys.push(x)
  end
  puts "  starting combinations..\n"
  combination(keys).sort.uniq.each do |combo|
    zipname = base_name
    puts "  --\n"
    modules = {}
    combo.each do |i|
      mod = values[i] 
      modules[mod] = plugins[mod]
      zipname += "_#{mod}"
    end    
    make_package("#{package_dir}#{skel_name}/plugins/", modules, dump_dir)
    puts "      making zip..\n"
    zip(package_dir, "../#{zip_dir}", zipname, "#{skel_name}")
    tidy("#{package_dir}#{skel_name}/plugins/*")
  end
  puts "<< end skel #{skel_name}\n"
end
puts "<< end main\n"
puts "-- tidy\n"
tidy("#{dump_dir}")
tidy("#{package_dir}")
puts "<< tidy complete\n"
