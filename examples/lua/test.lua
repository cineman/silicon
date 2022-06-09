console.log('Starting...')

local v = 1
local l = 1
for n = 1, 64 do
    local z = v + l
    v = l
    l = z
    console.log(z)
    silicon.sleep(50 * 1000) -- sleep for 50ms
end
